<?php
// This Configuration file holds the current Data Dictionary
// Tables in the Database are automatically setup (but not populated) using this file

$clqtables = array(

	'clqstring' => array(
		'clq_langcd', 'clq_text', 'clq_reference', 'clq_parent', 'clq_value', 'clq_image', 'clq_usage', 'clq_common', 'clq_type', 'clq_order', 'clq_extra', 'clq_options', 'clq_notes'
	),

	'clqdata' => array(
		'clq_langcd', 'clq_text', 'clq_reference', 'clq_parent', 'clq_value', 'clq_image', 'clq_usage', 'clq_common', 'clq_type', 'clq_order', 'clq_extra', 'clq_notes', 'clq_ownerid', 'clq_feed', 'clq_category', 'clq_keywords', 'clq_url', 'clq_authorid', 'clq_options', 'clq_datefrom', 'clq_dateto', 'clq_title', 'clq_summary', 'clq_revision', 'clq_display', 'clq_archive'
	),
	
	'clqitem' => array( // non language specific, equivalent to clqdata - code, locations, other single language uses - clq_blob could hold Flash etc.
		'clq_type', 'clq_reference', 'clq_order', 'clq_value', 'clq_blob', 'clq_parent', 'clq_title', 'clq_summary', 'clq_text', 'clq_image', 'clq_url', 'clq_options', 'clq_extra', 'clq_notes',
	),	
	
);