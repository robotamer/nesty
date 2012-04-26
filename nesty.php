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

use DB;
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
	 * An array that contains all children models
	 * that have been retrieved from the database.
	 *
	 * @var array
	 */
	public $children = array();

	/**
	 * Makes the current model a root nesty.
	 *
	 * @todo Allow existing objects to move to
	 *       be root objects.
	 *
	 * @return  bool
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

	/**
	 * Make the current model the first child of
	 * the given parent.
	 *
	 * @param   Nesty  $parent
	 * @return  Nesty
	 */
	public function first_child_of(Nesty &$parent)
	{
		return $this->child_of($parent, 'first');
	}

	/**
	 * Make the current model the last child of
	 * the given parent.
	 *
	 * @param   Nesty  $parent
	 * @return  Nesty
	 */
	public function last_child_of(Nesty &$parent)
	{
		return $this->child_of($parent, 'first');
	}

	/**
	 * Make the current model either the first or
	 * last child of the given parent.
	 *
	 * @param   Nesty  $parent
	 * @return  Nesty
	 */
	public function child_of(Nesty &$parent, $position)
	{
		if ( ! $parent->exists())
		{
			throw new NestyException('The parent Nesty model must exist before you can assign children to it.');
		}

		if ( ! in_array($position, array('first', 'last')))
		{
			throw new NestyException(sprintf('Position %s is not a valid position.', $position));
		}

		// Reset cached children
		$parent->children = array();

		// If we haven't been saved to the database before
		if ( ! $this->exists())
		{
			// Inserting as first child
			if ($position === 'first')
			{
				// Our left limit is 1 greater than the left limit
				// of the parent
				$this->{static::$nesty_cols['left']} = $parent->{static::$nesty_cols['left']} + 1;

				// Our right is 1 more than our left
				$this->{static::$nesty_cols['right']} = $parent->{static::$nesty_cols['left']} + 2;
			}


			// Inserting as last child
			else
			{
				// Our left limit is 1 greater than the current last
				// child's right limit, we will sit as the new last child.
				// This means also, that our left limit matches the parent's
				// current right limit. Confusing? Read over it again, it'll make
				// sense
				$this->{static::$nesty_cols['left']}  = $parent->{static::$nesty_cols['right']};
				$this->{static::$nesty_cols['right']} = $parent->{static::$nesty_cols['right']} + 1;
			}

			// Set our tree identifier to match the parent
			$this->{static::$nesty_cols['tree']} = $parent->{static::$nesty_cols['tree']};

			// Create a gap a gap in the tree that starts at
			// our left limit and is 2 wide (the width of an empty
			// nesty)
			$this->gap($this->{static::$nesty_cols['left']}, 2, $this->{static::$nesty_cols['tree']});

			$this->save();
		}

		// If we are existent in the database
		else
		{

		}
	}

	/*
	|--------------------------------------------------------------------------
	| Static Usage
	|--------------------------------------------------------------------------
	*/

	/**
	 * Create a gap in the tree.
	 *
	 * @param   int  $start
	 * @param   int  $size
	 * @param   int  $tree
	 * @return  void
	 */
	protected function gap($start, $size, $tree)
	{
		$this->query()
		      ->where(static::$nesty_cols['left'], '>=', $start)
		      ->where(static::$nesty_cols['tree'], '=', $tree)
		      ->update(array(
		      	static::$nesty_cols['left'] => DB::raw('`'.static::$nesty_cols['left'].'` + '.$size),
		      ));

		$this->query()
		      ->where(static::$nesty_cols['right'], '>=', $start)
		      ->where(static::$nesty_cols['tree'], '=', $tree)
		      ->update(array(
		      	static::$nesty_cols['right'] => DB::raw('`'.static::$nesty_cols['right'].'` + '.$size),
		      ));
	}
}