#
# Table structure for table 'tt_content'
#

CREATE TABLE tt_content (
	tx_srpuzzle_piece_size mediumint(11) unsigned DEFAULT '0' NOT NULL,
	tx_srpuzzle_offsetX mediumint(11) unsigned DEFAULT '0' NOT NULL,
	tx_srpuzzle_offsetY mediumint(11) unsigned DEFAULT '0' NOT NULL,
	tx_srpuzzle_link_after tinytext NOT NULL,
	tx_srpuzzle_popup_link tinytext NOT NULL,
);