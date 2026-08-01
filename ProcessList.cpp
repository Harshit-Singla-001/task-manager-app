#include "ProcessList.h"

Process::Process(PROCESSENTRY32 _pe32, PROCESS_MEMORY_COUNTERS _pmc)
    : pe32(_pe32), pmc(_pmc) {}

int Process::getPID() const {
    return pe32.th32ProcessID;
}

string Process::getImageName() const {
    return string(pe32.szExeFile);
}

int Process::getMemUsage() const {
    return static_cast<int>(pmc.WorkingSetSize / 1024);
}

// ---------- helper ----------
string ProcessList::toLower(string s) {
    transform(s.begin(), s.end(), s.begin(), ::tolower);
    return s;
}

// ---------- build process list ----------
void ProcessList::makeList() {
    listVec.clear();   // IMPORTANT FIX

    HANDLE hSnap = CreateToolhelp32Snapshot(TH32CS_SNAPPROCESS, 0);
    if (hSnap == INVALID_HANDLE_VALUE) return;

    PROCESSENTRY32 pe32;
    pe32.dwSize = sizeof(PROCESSENTRY32);

    if (Process32First(hSnap, &pe32)) {
        do {
            HANDLE hProcess = OpenProcess(
                PROCESS_QUERY_INFORMATION | PROCESS_VM_READ,
                FALSE,
                pe32.th32ProcessID
            );

            if (hProcess) {
                PROCESS_MEMORY_COUNTERS pmc;
                if (GetProcessMemoryInfo(hProcess, &pmc, sizeof(pmc))) {
                    listVec.push_back(Process(pe32, pmc));
                }
                CloseHandle(hProcess);
            }
        } while (Process32Next(hSnap, &pe32));
    }

    CloseHandle(hSnap);
}

// ---------- print ----------
void ProcessList::printList() {
    makeList();

    int choice;
    while (true) {
        cout << "\nSorting Options:\n1) Name  2) PID  3) Memory  4) Back\n";
        cin >> choice;

        if (choice == 4) break;

        switch (choice) {
        case 1:
            sortList([](const Process& a, const Process& b) {
                return a.getImageName() < b.getImageName();
            });
            break;
        case 2:
            sortList([](const Process& a, const Process& b) {
                return a.getPID() < b.getPID();
            });
            break;
        case 3:
            sortList([](const Process& a, const Process& b) {
                return a.getMemUsage() < b.getMemUsage();
            });
            break;
        default:
            cout << "Invalid choice\n";
            continue;
        }

        cout << setw(15) << left << "PID"
             << setw(40) << left << "Process Name"
             << setw(15) << right << "Memory(KB)\n";
        cout << "------------------------------------------------------------\n";

        for (const auto& p : listVec) {
            cout << setw(15) << left << p.getPID()
                 << setw(40) << left << p.getImageName()
                 << setw(15) << right << p.getMemUsage() << "\n";
        }
    }
}

// ---------- sort ----------
template<typename Compare>
void ProcessList::sortList(Compare comp) {
    sort(listVec.begin(), listVec.end(), comp);
}

// ---------- delete ----------
void ProcessList::deleteProcess() {
    makeList();

    int choice;
    cout << "\nDelete Options:\n1) By PID  2) By Name  3) Back\n";
    cin >> choice;

    if (choice == 3) return;

    DWORD pid = 0;
    string name;

    if (choice == 1) {
        cout << "Enter PID: ";
        cin >> pid;
    } else if (choice == 2) {
        cout << "Enter process name: ";
        cin.ignore();
        getline(cin, name);
        name = toLower(name);
    } else {
        cout << "Invalid choice\n";
        return;
    }

    int terminatedCount = 0;
    int failedCount = 0;

    for (const auto& p : listVec) {
        bool match =
            (choice == 1 && p.getPID() == pid) ||
            (choice == 2 && toLower(p.getImageName()) == name);

        if (match) {
            HANDLE hProcess = OpenProcess(PROCESS_TERMINATE, FALSE, p.getPID());
            if (hProcess) {
                if (TerminateProcess(hProcess, 0)) {
                    terminatedCount++;
                } else {
                    failedCount++;
                    cout << "Failed to terminate PID " << p.getPID() << ". Error: " << GetLastError() << "\n";
                }
                CloseHandle(hProcess);
            } else {
                failedCount++;
                cout << "Access denied to open PID " << p.getPID() << " for termination. Error: " << GetLastError() << "\n";
            }
            if (choice == 1) {
                break;
            }
        }
    }

    if (terminatedCount > 0) {
        cout << "Successfully terminated " << terminatedCount << " process(es).\n";
    } else if (failedCount == 0) {
        cout << "Process not found.\n";
    }
}

// ---------- restart (FIXED CreateProcess ERROR HERE) ----------
void ProcessList::restartProcess() {
    makeList();

    int choice;
    cout << "\nRestart Options:\n1) By PID  2) By Name  3) Back\n";
    cin >> choice;

    if (choice == 3) return;

    DWORD pid = 0;
    string name;

    if (choice == 1) {
        cout << "Enter PID: ";
        cin >> pid;
    } else if (choice == 2) {
        cout << "Enter process name: ";
        cin.ignore();
        getline(cin, name);
        name = toLower(name);
    } else {
        cout << "Invalid choice\n";
        return;
    }

    for (const auto& p : listVec) {
        bool match =
            (choice == 1 && p.getPID() == pid) ||
            (choice == 2 && toLower(p.getImageName()) == name);

        if (match) {
            HANDLE hProcess = OpenProcess(PROCESS_TERMINATE | PROCESS_QUERY_LIMITED_INFORMATION, FALSE, p.getPID());
            if (!hProcess) {
                hProcess = OpenProcess(PROCESS_TERMINATE | PROCESS_QUERY_INFORMATION, FALSE, p.getPID());
            }

            if (hProcess) {
                vector<char> pathBuf(32768);
                DWORD size = pathBuf.size();
                string fullPath = p.getImageName(); // default fallback

                typedef BOOL (WINAPI *QueryFullProcessImageNameA_t)(HANDLE, DWORD, LPSTR, PDWORD);
                QueryFullProcessImageNameA_t pQueryFullProcessImageNameA = 
                    (QueryFullProcessImageNameA_t)GetProcAddress(GetModuleHandleA("kernel32.dll"), "QueryFullProcessImageNameA");

                if (pQueryFullProcessImageNameA && pQueryFullProcessImageNameA(hProcess, 0, pathBuf.data(), &size)) {
                    fullPath = string(pathBuf.data(), size);
                } else {
                    DWORD bytesRead = GetModuleFileNameExA(hProcess, NULL, pathBuf.data(), pathBuf.size());
                    if (bytesRead > 0) {
                        fullPath = string(pathBuf.data(), bytesRead);
                    }
                }

                if (TerminateProcess(hProcess, 0)) {
                    CloseHandle(hProcess);
                    cout << "Terminated process successfully. Starting new instance...\n";

                    vector<char> cmdBuf(fullPath.begin(), fullPath.end());
                    cmdBuf.push_back('\0');

                    STARTUPINFO si = { sizeof(si) };
                    PROCESS_INFORMATION pi;

                    if (CreateProcess(
                            NULL,
                            cmdBuf.data(),
                            NULL,
                            NULL,
                            FALSE,
                            0,
                            NULL,
                            NULL,
                            &si,
                            &pi)) {

                        CloseHandle(pi.hProcess);
                        CloseHandle(pi.hThread);
                        cout << "Process restarted successfully from: " << fullPath << "\n";
                    } else {
                        cout << "Restart failed (CreateProcess failed). Error: " << GetLastError() << "\n";
                    }
                } else {
                    cout << "Failed to terminate process for restart. Error: " << GetLastError() << "\n";
                    CloseHandle(hProcess);
                }
                return;
            } else {
                cout << "Access denied to open process for restart. Error: " << GetLastError() << "\n";
                return;
            }
        }
    }

    cout << "Process not found.\n";
}

// ---------- add process (FIXED PROPERLY) ----------
bool ProcessList::addProcess(const string& processName) {
    vector<char> cmd(processName.begin(), processName.end());
    cmd.push_back('\0');

    STARTUPINFO si = { sizeof(si) };
    PROCESS_INFORMATION pi;

    if (!CreateProcess(
            NULL,
            cmd.data(),   // ✅ writable buffer
            NULL,
            NULL,
            FALSE,
            0,
            NULL,
            NULL,
            &si,
            &pi)) {
        cout << "Failed to start process. Error: " << GetLastError() << "\n";
        return false;
    }

    CloseHandle(pi.hProcess);
    CloseHandle(pi.hThread);
    return true;
}
