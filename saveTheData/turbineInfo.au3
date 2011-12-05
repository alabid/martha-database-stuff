; This script can automatically collect data, store data into excel and compile data.

; CONDITIONS of running this script:
;;; When writing this script, we assumed that VestasOnline shortcut, and excel shortcut are on the desktop, and
;;; this script file, the python code and Hourly Wind Turbine Data.xlsx are in "My Documents".
;;; We also assumed that there are no files named current.txt, Hourly Wind Turbine Data.txt in "My Documents".
;;; If there are, please delete them before running this script.

; WHAT TO DO IF THE SCRIPT STOPS RUNNING, but probably can finish:
;;; Can either treat this situation as failure and follow the steps for failure,
;;; or help the script to continue

; How to help the script to continue if it stops:
;;; If it cannot dial up to the server, dial up manually.
;;; If it cannot save the excel file, save it manually. Choose yes when saving.
;;; If it cannot close the excel file, (after saving), close it manually. Choose no when closing.
;;; If it stops after copy the data from VestasOnline software (does not open excel), click on command line window to make it active
;;; Other situations: Try to make relevant windows active (software windows, command line window, and excel)
;;; If fail, please follow the steps for failure.

; WHAT TO DO IF THE SCRIPT FAILS TO FINISH:
;;; 1. stop executing this script
;;; 2. delete current.txt, Hourly Wind Turbine Data.txt in "My Documents"
;;; 3. Rerun this script


; Can stop the script manually when it is neither collecting nor compiling data
While True
; Only run the script when it is 6 am or 8 pm. Change here if you want to run the script in different time.

If @HOUR = 6 Or  @HOUR = 20 Then
; open command line windows and run VestasOnline software
Run("cmd")
WinWaitActive("C:\WINDOWS\system32\cmd.exe")
; handle error. This error may occur when the script fails to open command line windows
If @error<> 0 Then
	MsgBox(0,"Error","Error! Please restart this script.")
	Exit
EndIf
; go to desktop on command line.
Send("cd ..")
Send("{ENTER}")
Send("cd Desktop")
Send("{ENTER}")
; open VestasOnline software
Send("VestasOnline.lnk")
Send("{ENTER}")

If @error<> 0 Then
	MsgBox(0,"Error","Error! Please restart this script.")
	Exit
EndIf
WinWaitActive("VestasOnline Business")

; open connection manager and dial up
Send("!f")
Send("{ENTER}")
If @error<> 0 Then
	MsgBox(0,"Error","Error! Can not open VestasOnline Business.")
	Exit
EndIf
WinWaitActive("Connection Manager")
; choose Carleton College.
Send("{DOWN}")
Send("{ENTER}")

WinWaitActive("VestasOnline Business, Carleton College")
If @error<> 0 Then
	MsgBox(0,"Error","Error! Can not dial up.")
	Exit
EndIf

; select hourly data from database.
Send("!u")
Send("{DOWN}")
Send("{DOWN}")
Send("{RIGHT}")
Send("{DOWN}")
Send("{ENTER}")

; select all attributes
WinWaitActive("DCE3 Hour data, T69122","Select")
If @error<> 0 Then
	MsgBox(0,"Error","Error! Cannot select attributes")
	Exit
EndIf
Send("{TAB}")
Sleep(100)
Dim $counter
For $counter = 0 to 31 Step 1
	Send("{SPACE}")
	Sleep(100)
	Send("{DOWN}")
	Sleep(100)
Next
; select the last attribute and move to the table.
Send("{SPACE}")
Sleep(100)
Send("{TAB}")
Sleep(100)
Send("{RIGHT}")
Sleep(100)
Send("{RIGHT}")

; go to table and grab data
WinWaitActive("DCE3 Hour data, T69122","Table")
If @error<> 0 Then
	MsgBox(0,"Error","Error! Can not go to table.")
	Exit
EndIf

; wait for 10 seconds before data are ready. It 10 seconds is not enough, sleep 20 seconds.
Sleep(10000) ; 10 seconds = 10000 milliseconds.

; copy data
Send("{TAB}")
Send("^c")
Send("{DOWN}")

; close VestasOnline software
WinClose("DCE3 Hour data, T69122","Table")
WinClose("DCE3 Hour data, T69122","Select")
WinClose("Connection Manager")
WinClose("VestasOnline Business, Carleton College")

; select yes when closing
Send("!y")
Send("{ENTER}")

If @error<> 0 Then
	MsgBox(0,"Error","Error! Can not close VestasOnline. Please close it manually and click on command line window")
EndIf

; back to command line window
WinWaitActive("C:\WINDOWS\system32\cmd.exe")

; open excel
Send("""Microsoft Office Excel 2007.lnk""")
Send("{ENTER}")
WinWaitActive("Microsoft Excel")

; paste the data and save it as current.txt
Send("^v")
Send("^s")
WinWaitActive("Save As")
Send("!n")
Send("current")
Send("{TAB}")
Sleep(100)
; choose txt format by using down key.
For $counter = 0 to 10 Step 1
	Send("{DOWN}")
	Sleep(100)
Next
Send("!s")

; select yes when saving csv.
WinWaitActive("Microsoft Office Excel","multiple sheets")
Send("{ENTER}")
WinWaitActive("Microsoft Office Excel","format")
Send("!y")
If @error<> 0 Then
	MsgBox(0,"Error","Error! Cannot save current.txt. Save it manually, please.")
EndIf

; open hourly wind turbine data and save it in txt format
WinWaitActive("Microsoft Excel - current.txt")
Send("!f")
Sleep(500) ; let the computer respond
Send("{DOWN}")
Sleep(500) ; let the computer respond
Send("{ENTER}")
WinWaitActive("Open")
Send("Hourly Wind Turbine Data.xlsx")
Send("!o")


WinWaitActive("Microsoft Excel - Hourly Wind Turbine Data.xlsx")
; save as txt format here
Send("!f")
For $counter = 0 to 2 Step 1
	Send("{DOWN}")
	Sleep(100)
Next
Send("{ENTER}")
WinWaitActive("Save As")
Send("{TAB}")
Sleep(100)
; select txt format
For $counter = 0 to 10 Step 1
	Send("{DOWN}")
	Sleep(100)
Next
Send("!s")

; select yes when saving txt.
WinWaitActive("Microsoft Office Excel","&Yes")
Send("!y")

; close excel
; close Hourly Wind Turbine Data.txt
; NOTE: change here if the filename is changed
WinClose("Microsoft Excel - Hourly Wind Turbine Data.txt")
WinWaitActive("Microsoft Office Excel","save the changes")
Send("!n")

; close the current.csv
WinWaitActive("Microsoft Office Excel","save the changes")
Send("!n")

;run python code here
WinWaitActive("C:\WINDOWS\system32\cmd.exe")
Send("cd ../""My Documents""")
Send("{ENTER}")
Send("python removeDuplicates.py")
Send("{ENTER}")
; wait until python has finished to be safe
Sleep(1000)

; convert txt back to excel format
WinWaitActive("C:\WINDOWS\system32\cmd.exe")
; open excel
Send("cd ../Desktop")
Send("{ENTER}")
Send("""Microsoft Office Excel 2007.lnk""")
Send("{ENTER}")
WinWaitActive("Microsoft Excel")
Send("!f")
Sleep(500)
Send("{DOWN}")
Sleep(500)
Send("{ENTER}")

; open Hourly Wind Turbine Data.txt
WinWaitActive("Open")
Send("Hourly Wind Turbine Data.txt")
Send("!o")

; import txt into excel
WinWaitActive("Text Import Wizard - Step 1 of 3")
Send("!f")
WinWaitActive("Microsoft Excel - Hourly Wind Turbine Data.txt")

; select "Save As"
Send("!f")
For $counter = 0 to 2 Step 1
	Send("{DOWN}")
	Sleep(100)
Next
Send("{ENTER}")
WinWaitActive("Save As")
Send("{TAB}")

; select xlsx format
For $counter = 0 to 10 Step 1
	Send("{UP}")
	Sleep(100)
Next
Send("!s")
WinWaitActive("Microsoft Office Excel","already exists")
Send("!y")
Sleep(1000)
WinClose("Microsoft Excel - Hourly Wind Turbine Data.xlsx")

; delete temporary files
WinWaitActive("C:\WINDOWS\system32\cmd.exe")
Send("DEL ..\""My Documents""\current.txt")
Send("{ENTER}")
Send("DEL ..\""My Documents""\""Hourly Wind Turbine Data.txt""")
Send("{ENTER}")

; close command line window and this cycle is complete.
WinClose("C:\WINDOWS\system32\cmd.exe")
EndIf
Sleep(3500000) ; sleep for an hour and then check the condition again.
WEnd
