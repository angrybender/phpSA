<?php
/**
 * todo autoloader
 * @author k.vagin
 */

include 'analisator/def.types_of_checkers.php';

include 'analisator/ParentChecker.php';
include 'analisator/ParentExtractor.php';
include 'analisator/Report.php';

include 'core/Utils.php';
include 'core/Tokenizer.php';
include 'core/Procedures.php';
include 'core/Expressions.php';
include 'core/Variables.php';

include 'checkers/Conditions.php';
include 'checkers/DateBadOperation.php';

include 'extractors/Procedure.php';
include 'extractors/Conditions.php';