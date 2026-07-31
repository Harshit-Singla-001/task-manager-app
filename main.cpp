#include <iostream>
#include "ProcessList.h"

using namespace std;

int main() {
    ProcessList processList;

    while (true) {
        int choice;
        cout << "\nOptions:\n"
             << "1) Show all processes\n"
             << "2) Add new process\n"
             << "3) Delete process\n"
             << "4) Restart process\n"
             << "5) Exit\n";
        cin >> choice;

        switch (choice) {
        case 1:
            processList.printList();
            break;
        case 2: {
            string name;
            cout << "Enter process name (e.g. notepad.exe): ";
            cin.ignore();
            getline(cin, name);
            processList.addProcess(name);
            break;
        }
        case 3:
            processList.deleteProcess();
            break;
        case 4:
            processList.restartProcess();
            break;
        case 5:
            return 0;
        default:
            cout << "Invalid choice\n";
        }
    }
}
