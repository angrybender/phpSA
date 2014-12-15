<?php
/**
 * тут будет связь между типом ноды и классом оператора, который ее должен обработать
 * @author k.vagin
 */

namespace Core\Flow\Trace;


abstract class Locator
{
	private static $services = array(
		'Expr_Assign' 		=> 'Expr_Assign',
		'Expr_Array' 		=> 'Expr_Array',

		'Stmt_If' 			=> 'Blocks\\Stmt_If',
		'Stmt_For' 			=> 'Blocks\\Stmt_For',
		'Stmt_While' 		=> 'Blocks\\Stmt_While',
		'Stmt_Do' 			=> 'Blocks\\Stmt_While', // циклы с пред- и пост- условиями технически одинаковы
		'Stmt_Foreach' 		=> 'Blocks\\Stmt_Foreach',
		'Stmt_Switch' 		=> 'Blocks\\Stmt_Switch',

		'Expr_ConstFetch'	=> 'Expr_ConstFetch',
		'Expr_Variable'		=> 'Expr_Variable',
		'Expr_Greater'		=> 'Comparations\\Expr_Greater',
		'Expr_Smaller'		=> 'Comparations\\Expr_Smaller',
		'Scalar*'			=> 'Scalar_Operator',

		'Stmt_Echo'			=> 'Statements\\Stmt_Echo',
		'Stmt_Break'		=> 'Statements\\Stmt_Dummy',
		'Stmt_Continue'		=> 'Statements\\Stmt_Dummy',
		'Stmt_Goto'			=> 'Statements\\Stmt_Dummy',
		'Stmt_Label'		=> 'Statements\\Stmt_Dummy',
		'Stmt_Declare'		=> 'Statements\\Stmt_Dummy',

		'Expr_Plus'			=> 'Arifmetics\\Additive',
		'Expr_Minus'		=> 'Arifmetics\\Additive',
		'Expr_PostInc'		=> 'Arifmetics\\Unary',
		'Expr_PostDec'		=> 'Arifmetics\\Unary',
		'Expr_PreInc'		=> 'Arifmetics\\Unary',
		'Expr_PreDec'		=> 'Arifmetics\\Unary',
		'Expr_UnaryMinus'	=> 'Arifmetics\\Unary',
		'Expr_Div'			=> 'Arifmetics\\Div',
		'Expr_Mul'			=> 'Arifmetics\\Additive',
		'Expr_Mod'			=> 'Arifmetics\\Expr_Mod',
		'Expr_Concat'		=> 'Expr_Concat',
		'Expr_BitwiseOr'	=> 'Bit\\Operator',
		'Expr_BitwiseAnd'	=> 'Bit\\Operator',
		'Expr_BitwiseXor'	=> 'Bit\\Operator',
		'Expr_ShiftLeft'	=> 'Bit\\Operator',
		'Expr_ShiftRight'	=> 'Bit\\Operator',
		'Expr_BitwiseNot'	=> 'Bit\\Operator',

		'Expr_FuncCall'		=> 'Blocks\\Expr_FuncCall',
	);

	/**
	 * @param $type
	 * @return \Core\Flow\Trace\Operators\IOperator
	 * @throws Exceptions\Locator
	 */
	public static function locate($type)
	{
		$operator = null;
		if (isset(self::$services[$type])) {
			$operator = self::$services[$type];
		}
		else {
			$namespaces = explode('_', $type);
			$module = $namespaces[0] . '*';
			$operator = isset(self::$services[$module]) ? self::$services[$module] : null;
		}

		if (empty($operator)) {
			throw new Exceptions\Locator($type);
		}

		$full_path = '\\Core\\Flow\\Trace\\Operators\\' . $operator;
		return new $full_path;
	}
}