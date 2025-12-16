; AutoHotkey v1.1
; Lista todos os arquivos e pastas a partir da pasta do script (recursivo)
; e grava em um TXT na pasta do script.

#NoEnv
#SingleInstance Force
SetBatchLines, -1
FileEncoding, UTF-8

root := A_ScriptDir
outFile := A_ScriptDir "\lista_pastas_e_arquivos.txt"

; Garante arquivo "zerado" antes de gerar
FileDelete, %outFile%

; Buffer para reduzir escrita em disco
buf := ""
flushEvery := 5000  ; caracteres aproximados para flush

; 1) Pastas (inclui subpastas) - retorna pastas como "FileName"
Loop, Files, % root "\*", D R
{
    buf .= A_LoopFileFullPath "`r`n"
    if (StrLen(buf) >= flushEvery)
    {
        FileAppend, %buf%, %outFile%
        buf := ""
    }
}

; 2) Arquivos (inclui subpastas)
Loop, Files, % root "\*", F R
{
    buf .= A_LoopFileFullPath "`r`n"
    if (StrLen(buf) >= flushEvery)
    {
        FileAppend, %buf%, %outFile%
        buf := ""
    }
}

; Flush final
if (buf != "")
    FileAppend, %buf%, %outFile%

MsgBox, 64, Concluído, Lista gerada em:`n%outFile%
ExitApp
