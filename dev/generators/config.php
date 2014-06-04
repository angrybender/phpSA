<?php
/**
 *
 * @author k.vagin
 */

file_put_contents(__DIR__ . '/../../config.ini',
"; выводить ли синтаксические ошибки?
[syntax_error]
print = true

; чекеры надо пропустить?
[skipped_politics]
all = true

; имя класса регистрочуствительно! namespace не нужен
[skipped_checkers_by_class_name]
VarUndefined = true
");