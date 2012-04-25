<?php
/**
 * Part of the Nesty bundle for Laravel.
 *
 * @package    Nesty
 * @version    1.0
 * @author     Cartalyst LLC
 * @license    MIT License
 * @copyright  2012 Cartalyst LLC
 * @link       http://cartalyst.com
 */
namespace Nesty;

use Crud;

/**
 * Nesty model class.
 *
 * @author Ben Corlett
 */
class Nesty extends Crud
{
	/**
	 * Array of nesty column default names
	 * 
	 * We are using `lft` and `rgt` because
	 * `left` and `right` are reserved words
	 * in many databases, including MySQL
	 * 
	 * @var array
	 */
	protected static $nesty_cols = array(
		'left'  => 'lft',
		'right' => 'rgt',
		'name'  => 'name',
		'tree'  => 'tree_id',
	);

	/**
	 * Makes the current model a root nesty.
	 *
	 * @todo Allow existing objects to move to
	 *       be root objects
	 *
	 * @return  Nesty
	 */
	public function root()
	{
		// Set the left and right limit of the nesty
		$this->{static::$nesty_cols['left']}  = 1;
		$this->{static::$nesty_cols['right']} = 2;

		// Tree identifier
		$this->{static::$nesty_cols['tree']} = (int) $this->query()->max(static::$nesty_cols['tree']) + 1;

		return $this->save();
	}
}