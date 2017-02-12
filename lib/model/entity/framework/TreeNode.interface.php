<?php

/**
 * TreeNode.interface.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Object with zero or one parent and zero or more children.
 * 
 */
interface TreeNode {

	/**
	 * @return boolean false if node is root; true otherwise
	 */
	public function hasParent();

	/**
	 * @return TreeNode
	 */
	public function getParent();

	/**
	 * @return boolean false if node is leaf; true otherwise
	 */
	public function hasChildren();

	/**
	 * @return TreeNode[]
	 */
	public function getChildren();

}
