<?php
/**
 *
 * @author k.vagin
 */

file_put_contents(__DIR__ . '/../../config.ini',
"
; чекеры надо пропустить?
[skipped_politics]
all = true

; имя класса регистрочуствительно! namespace не нужен
[skipped_checkers_by_class_name]
VarUndefined = true
");