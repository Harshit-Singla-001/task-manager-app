import sys
import os
import subprocess
import psutil
from PySide6.QtCore import Qt, QTimer
from PySide6.QtWidgets import (
    QApplication, QMainWindow, QWidget, QVBoxLayout, QHBoxLayout,
    QTableWidget, QTableWidgetItem, QPushButton, QLineEdit,
    QMessageBox, QInputDialog, QHeaderView, QLabel, QStatusBar,
    QComboBox
)

class NumericTableWidgetItem(QTableWidgetItem):
    """Custom QTableWidgetItem that enables proper numeric sorting."""
    def __init__(self, value):
        super().__init__()
        self.setData(Qt.ItemDataRole.EditRole, value)

class TaskManager(QMainWindow):
    def __init__(self):
        super().__init__()
        self.setWindowTitle("Task Manager")
        self.resize(800, 600)
        self.init_ui()
        self.refresh_process_list()

        # Setup real-time updates (every 1 second)
        self.timer = QTimer()
        self.timer.timeout.connect(self.refresh_process_list)
        self.timer.start(1000)

    def init_ui(self):
        # Apply premium dark mode styles using Catppuccin Mocha theme
        self.setStyleSheet("""
            QMainWindow {
                background-color: #1e1e2e;
            }
            QWidget {
                font-family: 'Segoe UI', Arial, sans-serif;
                color: #cdd6f4;
            }
            QLineEdit {
                background-color: #313244;
                border: 1px solid #45475a;
                border-radius: 6px;
                padding: 8px 12px;
                font-size: 14px;
                color: #cdd6f4;
            }
            QLineEdit:focus {
                border: 1px solid #89b4fa;
            }
            QTableWidget {
                background-color: #1e1e2e;
                alternate-background-color: #252538;
                gridline-color: #313244;
                border: 1px solid #313244;
                border-radius: 8px;
                color: #cdd6f4;
                font-size: 13px;
            }
            QTableWidget::item:selected {
                background-color: #313244;
                color: #89b4fa;
                font-weight: bold;
            }
            QHeaderView::section {
                background-color: #313244;
                color: #89b4fa;
                padding: 6px;
                border: none;
                font-weight: bold;
                font-size: 13px;
            }
            QPushButton {
                background-color: #89b4fa;
                color: #11111b;
                border: none;
                border-radius: 6px;
                padding: 8px 16px;
                font-weight: bold;
                font-size: 13px;
            }
            QPushButton:hover {
                background-color: #b4befe;
            }
            QPushButton:pressed {
                background-color: #74c7ec;
            }
            QPushButton#dangerButton {
                background-color: #f38ba8;
                color: #11111b;
            }
            QPushButton#dangerButton:hover {
                background-color: #f9e2af;
            }
            QPushButton#dangerButton:pressed {
                background-color: #eba0ac;
            }
            QComboBox {
                background-color: #313244;
                border: 1px solid #45475a;
                border-radius: 6px;
                padding: 6px 12px;
                color: #cdd6f4;
                font-size: 13px;
                min-width: 150px;
            }
            QComboBox:focus {
                border: 1px solid #89b4fa;
            }
            QComboBox QAbstractItemView {
                background-color: #1e1e2e;
                selection-background-color: #313244;
                selection-color: #89b4fa;
                color: #cdd6f4;
                border: 1px solid #45475a;
            }
            QStatusBar {
                background-color: #11111b;
                color: #a6adc8;
            }
        """)

        # Central Widget
        central_widget = QWidget()
        self.setCentralWidget(central_widget)
        main_layout = QVBoxLayout(central_widget)
        main_layout.setContentsMargins(15, 15, 15, 15)
        main_layout.setSpacing(12)

        # Top Bar (Search and Add Process)
        top_layout = QHBoxLayout()
        self.search_bar = QLineEdit()
        self.search_bar.setPlaceholderText("🔍 Search processes by name or PID...")
        self.search_bar.textChanged.connect(self.filter_processes)
        top_layout.addWidget(self.search_bar)

        self.btn_add = QPushButton("➕ Run New Process")
        self.btn_add.clicked.connect(self.add_process)
        top_layout.addWidget(self.btn_add)
        main_layout.addLayout(top_layout)

        # Process Table
        self.table = QTableWidget()
        self.table.setColumnCount(3)
        self.table.setHorizontalHeaderLabels(["PID", "Process Name", "Memory Usage (KB)"])
        self.table.setSelectionBehavior(QTableWidget.SelectionBehavior.SelectRows)
        self.table.setSelectionMode(QTableWidget.SelectionMode.SingleSelection)
        self.table.setEditTriggers(QTableWidget.EditTrigger.NoEditTriggers)
        self.table.setAlternatingRowColors(True)
        self.table.setSortingEnabled(True)

        # Table Headers Setup
        header = self.table.horizontalHeader()
        header.setSectionResizeMode(0, QHeaderView.ResizeMode.ResizeToContents)
        header.setSectionResizeMode(1, QHeaderView.ResizeMode.Stretch)
        header.setSectionResizeMode(2, QHeaderView.ResizeMode.ResizeToContents)
        
        # Intercept header click to keep combo box in sync and enforce ascending order
        header.sectionClicked.connect(self.sync_combo_box_to_header)

        main_layout.addWidget(self.table)

        # Bottom Bar (Control Buttons)
        bottom_layout = QHBoxLayout()
        
        self.btn_refresh = QPushButton("🔄 Force Refresh")
        self.btn_refresh.clicked.connect(self.refresh_process_list)
        bottom_layout.addWidget(self.btn_refresh)

        # Single Sort Dropdown (PID, Name, Memory - Ascending Only)
        self.sort_combo = QComboBox()
        self.sort_combo.addItems(["Sort by PID", "Sort by Name", "Sort by Memory"])
        self.sort_combo.currentIndexChanged.connect(self.apply_combo_sort)
        bottom_layout.addWidget(self.sort_combo)

        self.btn_restart = QPushButton("🔁 Restart Process")
        self.btn_restart.clicked.connect(self.restart_process)
        bottom_layout.addWidget(self.btn_restart)

        bottom_layout.addStretch()

        self.btn_terminate = QPushButton("🛑 End Process")
        self.btn_terminate.setObjectName("dangerButton")
        self.btn_terminate.clicked.connect(self.terminate_process)
        bottom_layout.addWidget(self.btn_terminate)

        main_layout.addLayout(bottom_layout)

        # Status Bar
        self.status_bar = QStatusBar()
        self.setStatusBar(self.status_bar)

    def refresh_process_list(self):
        # Save current selection
        selected_pid = self.get_selected_pid()
        
        # Read sort column from combo box selection
        sort_column = self.sort_combo.currentIndex()
        if sort_column < 0 or sort_column > 2:
            sort_column = 0
        
        # Disable sorting to prevent constant row swapping during updates
        self.table.setSortingEnabled(False)

        # Get current processes
        current_procs = {}
        for proc in psutil.process_iter():
            try:
                name = proc.name()
                pid = proc.pid
                mem_info = proc.memory_info()
                mem_kb = int(mem_info.rss / 1024)
                current_procs[pid] = (name, mem_kb)
            except (psutil.NoSuchProcess, psutil.AccessDenied, psutil.ZombieProcess):
                continue

        # Get PIDs currently in the table
        table_pids = {}
        for row in range(self.table.rowCount()):
            pid_item = self.table.item(row, 0)
            if pid_item:
                pid = int(pid_item.data(Qt.ItemDataRole.EditRole))
                table_pids[pid] = row

        # Remove processes that no longer exist
        rows_to_remove = []
        for pid, row in table_pids.items():
            if pid not in current_procs:
                rows_to_remove.append(row)
        
        for row in sorted(rows_to_remove, reverse=True):
            self.table.removeRow(row)

        # Re-index table rows after removal
        table_pids = {}
        for row in range(self.table.rowCount()):
            pid_item = self.table.item(row, 0)
            if pid_item:
                pid = int(pid_item.data(Qt.ItemDataRole.EditRole))
                table_pids[pid] = row

        # Update existing processes and insert new ones
        for pid, (name, mem_kb) in current_procs.items():
            if pid in table_pids:
                row = table_pids[pid]
                # Update memory usage
                mem_item = self.table.item(row, 2)
                if mem_item:
                    mem_item.setData(Qt.ItemDataRole.EditRole, mem_kb)
            else:
                # Add new process
                row = self.table.rowCount()
                self.table.insertRow(row)
                
                pid_item = NumericTableWidgetItem(pid)
                pid_item.setTextAlignment(Qt.AlignmentFlag.AlignLeft | Qt.AlignmentFlag.AlignVCenter)
                
                name_item = QTableWidgetItem(name)
                
                mem_item = NumericTableWidgetItem(mem_kb)
                mem_item.setTextAlignment(Qt.AlignmentFlag.AlignRight | Qt.AlignmentFlag.AlignVCenter)
                
                self.table.setItem(row, 0, pid_item)
                self.table.setItem(row, 1, name_item)
                self.table.setItem(row, 2, mem_item)

        # Restore sorting (enforce Ascending Order only)
        self.table.setSortingEnabled(True)
        self.table.sortByColumn(sort_column, Qt.SortOrder.AscendingOrder)

        # Restore selection
        if selected_pid is not None:
            for row in range(self.table.rowCount()):
                pid_item = self.table.item(row, 0)
                if pid_item and int(pid_item.data(Qt.ItemDataRole.EditRole)) == selected_pid:
                    self.table.selectRow(row)
                    break

        # Re-apply current filter
        self.filter_processes()

        # Update status bar info
        total_memory_kb = sum(info[1] for info in current_procs.values())
        sys_mem = psutil.virtual_memory()
        self.status_bar.showMessage(
            f"Processes: {len(current_procs)} | Total App Memory: {total_memory_kb / 1024:.1f} MB | System RAM: {sys_mem.percent}% used"
        )

    def filter_processes(self):
        search_text = self.search_bar.text().lower()
        for row in range(self.table.rowCount()):
            pid_item = self.table.item(row, 0)
            name_item = self.table.item(row, 1)
            
            if pid_item and name_item:
                pid = str(pid_item.data(Qt.ItemDataRole.EditRole)).lower()
                name = name_item.text().lower()
                
                if search_text in name or search_text in pid:
                    self.table.setRowHidden(row, False)
                else:
                    self.table.setRowHidden(row, True)

    def get_selected_pid(self):
        selected_ranges = self.table.selectedRanges()
        if not selected_ranges:
            return None
        row = selected_ranges[0].topRow()
        pid_item = self.table.item(row, 0)
        if pid_item:
            return int(pid_item.data(Qt.ItemDataRole.EditRole))
        return None

    def terminate_process(self):
        pid = self.get_selected_pid()
        if pid is None:
            QMessageBox.information(self, "No Selection", "Please select a process from the table first.")
            return

        reply = QMessageBox.question(
            self, "Confirm Termination",
            f"Are you sure you want to terminate PID {pid}?",
            QMessageBox.StandardButton.Yes | QMessageBox.StandardButton.No,
            QMessageBox.StandardButton.No
        )

        if reply == QMessageBox.StandardButton.Yes:
            try:
                proc = psutil.Process(pid)
                proc.terminate()
                try:
                    proc.wait(timeout=1.5)
                except psutil.TimeoutExpired:
                    proc.kill()
                QMessageBox.information(self, "Success", f"Process PID {pid} ended successfully.")
                self.refresh_process_list()
            except Exception as e:
                QMessageBox.critical(self, "Error", f"Failed to end process. Error:\n{str(e)}")

    def restart_process(self):
        pid = self.get_selected_pid()
        if pid is None:
            QMessageBox.information(self, "No Selection", "Please select a process from the table first.")
            return

        try:
            proc = psutil.Process(pid)
            exe_path = proc.exe()
            
            reply = QMessageBox.question(
                self, "Confirm Restart",
                f"Are you sure you want to restart PID {pid}?\nPath: {exe_path}",
                QMessageBox.StandardButton.Yes | QMessageBox.StandardButton.No,
                QMessageBox.StandardButton.No
            )

            if reply == QMessageBox.StandardButton.Yes:
                proc.terminate()
                try:
                    proc.wait(timeout=1.5)
                except psutil.TimeoutExpired:
                    proc.kill()
                
                subprocess.Popen([exe_path], shell=True)
                QMessageBox.information(self, "Success", f"Process restarted successfully.")
                self.refresh_process_list()

        except psutil.AccessDenied:
            QMessageBox.critical(
                self, "Error", 
                "Access Denied: Could not obtain the executable path. Windows does not permit reading path information for elevated/system processes from a standard user process."
            )
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Failed to restart process. Error:\n{str(e)}")

    def add_process(self):
        name, ok = QInputDialog.getText(
            self, "Run New Process", 
            "Enter process executable name or command path (e.g. notepad.exe):"
        )
        if ok and name.strip():
            try:
                subprocess.Popen(name.strip(), shell=True)
                QTimer.singleShot(1000, self.refresh_process_list)
            except Exception as e:
                QMessageBox.critical(self, "Error", f"Failed to run process. Error:\n{str(e)}")

    def apply_combo_sort(self):
        index = self.sort_combo.currentIndex()
        if 0 <= index <= 2:
            self.table.sortByColumn(index, Qt.SortOrder.AscendingOrder)

    def sync_combo_box_to_header(self, logical_index):
        # Force Ascending order only when table headers are clicked
        self.table.sortByColumn(logical_index, Qt.SortOrder.AscendingOrder)
        self.sort_combo.blockSignals(True)
        if 0 <= logical_index <= 2:
            self.sort_combo.setCurrentIndex(logical_index)
        self.sort_combo.blockSignals(False)

if __name__ == "__main__":
    app = QApplication(sys.argv)
    window = TaskManager()
    window.show()
    sys.exit(app.exec())