<?php

namespace CsrDelft\common\datatable\annotation;

/**
 * Annotation class for @DataTableColumn().
 *
 * @Annotation
 * @Target({"PROPERTY", "METHOD"})
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class DataTableColumn {
	/**
	 * @var boolean
	 */
	public $id;
	/**
	 * @var string
	 */
	public $name;
	public $data;
	public $title;
	/**
	 * @var string
	 * @Enum({"default", "check", "bedrag", "aanmeldFilter", "aanmeldingen", "totaalPrijs", "timeago", "filesize"})
	 */
	public $type;
	public $orderable;
	/**
	 * @var boolean
	 */
	public $searchable;
	public $defaultContent;
	/**
	 * @var boolean
	 */
	public $hidden;
}
