<?php
namespace Core;

/**
 * информация о языке
 * @author k.vagin
 */

class Repository
{
	/**
	 * переменные, которые имеют значение сразу по умолчанию
	 * @var array
	 */
	public static $predefined_vars = array(
		'$_POST',
		'$_SERVER',
		'$_GET',
		'$_POST',
		'$_FILES',
		'$_REQUEST',
		'$_SESSION',
		'$_ENV',
		'$_COOKIE',
		'$GLOBALS',
		'$php_errormsg',
		'$HTTP_RAW_POST_DATA',
		'$http_response_header',
		'$argc',
		'$argv',
		'$this'
	);

	// заполняется автоматически на основе описания стандартов пхп
	// такие функции, которые некоторый результат пишут в переданную им переменную
	public static $function_callback_into_variable = array(
		//'exec' => array(2,3), // возвращает значение в 2 и 3й аргументы
	);

	// заполняется автоматически на основе описания стандартов пхп
	// такие функции, которые результат пишут в переданную им переменную, но принимают бесконечное кол-во аргументов по ссылке
	public static $function_callback_into_variable_infinity = array(
		// 'sscanf' => 3, // с третьего аргумента и дальше
	);

	// заполняется автоматически на основе описания стандартов пхп
	// заполняется описанием функций
	public static $functions_prototypes = array(

	);

	/**
	 *коммутативные операторы
	 */
	public static $commutative_operators_Node_type = array(
		'Expr_Plus',
		'Expr_Mul',

		'Expr_BooleanAnd',
		'Expr_BooleanOr',

		'Expr_LogicalAnd',
		'Expr_LogicalXor',
		'Expr_LogicalOr',

		'Expr_BitwiseOr',
		'Expr_BitwiseXor',
		'Expr_BitwiseAnd',

		'Expr_NotEqual',
		'Expr_NotIdentical',
		'Expr_Identical',
		'Expr_Equal',
	);

	/**
	 * булевы операторы
	 * @var array
	 */
	public static $boolean_operators_Node_type = array(
		'Expr_BooleanAnd',
		'Expr_BooleanOr',
		'Expr_BooleanNot',
	);

	/**
	 * операторы сравнения
	 * @var array
	 */
	public static $compare_operators_Node_type = array(
		'Expr_Greater', 'Expr_SmallerOrEqual',
		'Expr_GreaterOrEqual', 'Expr_Smaller',
		'Expr_Smaller', 'Expr_GreaterOrEqual',
		'Expr_SmallerOrEqual', 'Expr_Greater',
		'Expr_NotEqual'			, 'Expr_Equal',
		'Expr_NotIdentical'		, 'Expr_Identical',
		'Expr_Equal'			, 'Expr_NotEqual',
		'Expr_Identical'		, 'Expr_NotIdentical',
	);

	/**
	 * операторы равенства
	 */
	public static $compare_eq_operators_Node_type = array(
		'Expr_NotEqual'			, 'Expr_Equal',
		'Expr_NotIdentical'		, 'Expr_Identical',
		'Expr_Equal'			, 'Expr_NotEqual',
		'Expr_Identical'		, 'Expr_NotIdentical',
	);

	/**
	 * обращение операторов при применении к нему NOT
	 * @var array
	 */
	public static $reverse_operators_rules = array(
		'Expr_BooleanAnd' 		=> 'Expr_BooleanOr',
		'Expr_BooleanOr' 		=> 'Expr_BooleanAnd',

		'Expr_Greater'			=> 'Expr_SmallerOrEqual',
		'Expr_GreaterOrEqual'	=> 'Expr_Smaller',
		'Expr_Smaller'			=> 'Expr_GreaterOrEqual',
		'Expr_SmallerOrEqual'	=> 'Expr_Greater',

		'Expr_NotEqual'			=> 'Expr_Equal',
		'Expr_NotIdentical'		=> 'Expr_Identical',
		'Expr_Equal'			=> 'Expr_NotEqual',
		'Expr_Identical'		=> 'Expr_NotIdentical',
	);
}