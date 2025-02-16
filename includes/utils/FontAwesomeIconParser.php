<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\MchLib\Utils;

final class FontAwesomeIconParser
{

	public static function generateFontAwesomeSelectList()
	{
		$arrAllIcons = \json_decode(self::getFontAwesomeIcons(), true);
		
		$arrIcons = array();
		
		foreach($arrAllIcons['icons'] as &$arrIconInfo)
		{
			unset($arrIconInfo['filter'], $arrIconInfo['created']);
			
			foreach($arrIconInfo['categories'] as $iconCategory)
			{
				if(empty($arrIcons[$iconCategory])){
					$arrIcons[$iconCategory] = array();
				}
				
				$iconInfo = array();
				
				$iconInfo['value'] = 'fa-' .  $arrIconInfo['id'];
				$iconInfo['text']  = $arrIconInfo['name'];
				
				$arrIcons[$iconCategory][] = $iconInfo;
				
				break;
			}
			
			
		}
		
		unset($arrAllIcons);
		
		$htmlSelectElement = '<select class="uc-font-awesome-icons-list">'; //<option value="">None</option>
		
		$categ = __('No Icon', 'ultra-community');
		$htmlSelectElement .= "<optgroup label=\"$categ\">";
		$htmlSelectElement .= "<option value=\"\">None</option>";
		$htmlSelectElement .= "</optgroup>";
		
		foreach ($arrIcons as $categ => $arrCategIcons)
		{
			$htmlSelectElement .= "<optgroup label=\"$categ\">";
			
			foreach ($arrCategIcons as $arrCategIcon)
			{
				$htmlSelectElement .= "<option value=\"{$arrCategIcon['value']}\">{$arrCategIcon['text']}</option>";
				//$htmlSelectElement .= "<option value=\"{$arrCategIcon['value']}\"></option>";
			}
			
			$htmlSelectElement .= "</optgroup>";
			
			unset($arrIcons[$categ], $arrCategIcons);
		}
		
		$htmlSelectElement .= '</select>';
		
		return 	$htmlSelectElement;
	}
	
	
	public static function getFontAwesomeIcons()
	{
		return '
{
  "icons": [
    {
      "filter": [
        "martini",
        "drink",
        "bar",
        "alcohol",
        "liquor"
      ],
      "name": "Glass",
      "unicode": "f000",
      "created": 1.0,
      "id": "glass",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "note",
        "sound"
      ],
      "name": "Music",
      "unicode": "f001",
      "created": 1.0,
      "id": "music",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "magnify",
        "zoom",
        "enlarge",
        "bigger"
      ],
      "name": "Search",
      "unicode": "f002",
      "created": 1.0,
      "id": "search",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "email",
        "support",
        "e-mail",
        "letter",
        "mail",
        "notification"
      ],
      "name": "Envelope Outlined",
      "unicode": "f003",
      "created": 1.0,
      "id": "envelope-o",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "love",
        "like",
        "favorite"
      ],
      "name": "Heart",
      "unicode": "f004",
      "created": 1.0,
      "id": "heart",
      "categories": [
        "Web Application Icons",
        "Medical Icons"
      ]
    },
    {
      "filter": [
        "award",
        "achievement",
        "night",
        "rating",
        "score",
        "favorite"
      ],
      "name": "Star",
      "unicode": "f005",
      "created": 1.0,
      "id": "star",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "award",
        "achievement",
        "night",
        "rating",
        "score",
        "favorite"
      ],
      "name": "Star Outlined",
      "unicode": "f006",
      "created": 1.0,
      "id": "star-o",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "person",
        "man",
        "head",
        "profile"
      ],
      "name": "User",
      "unicode": "f007",
      "created": 1.0,
      "id": "user",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "movie"
      ],
      "name": "Film",
      "unicode": "f008",
      "created": 1.0,
      "id": "film",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "blocks",
        "squares",
        "boxes",
        "grid"
      ],
      "name": "th-large",
      "unicode": "f009",
      "created": 1.0,
      "id": "th-large",
      "categories": [
        "Text Editor Icons"
      ]
    },
    {
      "filter": [
        "blocks",
        "squares",
        "boxes",
        "grid"
      ],
      "name": "th",
      "unicode": "f00a",
      "created": 1.0,
      "id": "th",
      "categories": [
        "Text Editor Icons"
      ]
    },
    {
      "filter": [
        "ul",
        "ol",
        "checklist",
        "finished",
        "completed",
        "done",
        "todo"
      ],
      "name": "th-list",
      "unicode": "f00b",
      "created": 1.0,
      "id": "th-list",
      "categories": [
        "Text Editor Icons"
      ]
    },
    {
      "filter": [
        "checkmark",
        "done",
        "todo",
        "agree",
        "accept",
        "confirm",
        "tick",
        "ok"
      ],
      "name": "Check",
      "unicode": "f00c",
      "created": 1.0,
      "id": "check",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "close",
        "exit",
        "x",
        "cross"
      ],
      "name": "Times",
      "unicode": "f00d",
      "created": 1.0,
      "id": "times",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "remove",
        "close"
      ]
    },
    {
      "filter": [
        "magnify",
        "zoom",
        "enlarge",
        "bigger"
      ],
      "name": "Search Plus",
      "unicode": "f00e",
      "created": 1.0,
      "id": "search-plus",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "magnify",
        "minify",
        "zoom",
        "smaller"
      ],
      "name": "Search Minus",
      "unicode": "f010",
      "created": 1.0,
      "id": "search-minus",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "on"
      ],
      "name": "Power Off",
      "unicode": "f011",
      "created": 1.0,
      "id": "power-off",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "graph",
        "bars"
      ],
      "name": "signal",
      "unicode": "f012",
      "created": 1.0,
      "id": "signal",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "settings"
      ],
      "name": "cog",
      "unicode": "f013",
      "created": 1.0,
      "id": "cog",
      "categories": [
        "Web Application Icons",
        "Spinner Icons"
      ],
      "aliases": [
        "gear"
      ]
    },
    {
      "filter": [
        "garbage",
        "delete",
        "remove",
        "trash",
        "hide"
      ],
      "name": "Trash Outlined",
      "unicode": "f014",
      "created": 1.0,
      "id": "trash-o",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "main",
        "house"
      ],
      "name": "home",
      "unicode": "f015",
      "created": 1.0,
      "id": "home",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "new",
        "page",
        "pdf",
        "document"
      ],
      "name": "File Outlined",
      "unicode": "f016",
      "created": 1.0,
      "id": "file-o",
      "categories": [
        "Text Editor Icons",
        "File Type Icons"
      ]
    },
    {
      "filter": [
        "watch",
        "timer",
        "late",
        "timestamp"
      ],
      "name": "Clock Outlined",
      "unicode": "f017",
      "created": 1.0,
      "id": "clock-o",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "street"
      ],
      "name": "road",
      "unicode": "f018",
      "created": 1.0,
      "id": "road",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "import"
      ],
      "name": "Download",
      "unicode": "f019",
      "created": 1.0,
      "id": "download",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "download"
      ],
      "name": "Arrow Circle Outlined Down",
      "unicode": "f01a",
      "created": 1.0,
      "id": "arrow-circle-o-down",
      "categories": [
        "Directional Icons"
      ]
    },
    {
      "id": "arrow-circle-o-up",
      "unicode": "f01b",
      "name": "Arrow Circle Outlined Up",
      "categories": [
        "Directional Icons"
      ],
      "created": 1.0
    },
    {
      "id": "inbox",
      "unicode": "f01c",
      "name": "inbox",
      "categories": [
        "Web Application Icons"
      ],
      "created": 1.0
    },
    {
      "id": "play-circle-o",
      "unicode": "f01d",
      "name": "Play Circle Outlined",
      "categories": [
        "Video Player Icons"
      ],
      "created": 1.0
    },
    {
      "filter": [
        "redo",
        "forward"
      ],
      "name": "Repeat",
      "unicode": "f01e",
      "created": 1.0,
      "id": "repeat",
      "categories": [
        "Text Editor Icons"
      ],
      "aliases": [
        "rotate-right"
      ]
    },
    {
      "filter": [
        "reload",
        "sync"
      ],
      "name": "refresh",
      "unicode": "f021",
      "created": 1.0,
      "id": "refresh",
      "categories": [
        "Web Application Icons",
        "Spinner Icons"
      ]
    },
    {
      "filter": [
        "ul",
        "ol",
        "checklist",
        "finished",
        "completed",
        "done",
        "todo"
      ],
      "name": "list-alt",
      "unicode": "f022",
      "created": 1.0,
      "id": "list-alt",
      "categories": [
        "Text Editor Icons"
      ]
    },
    {
      "filter": [
        "protect",
        "admin",
        "security"
      ],
      "name": "lock",
      "unicode": "f023",
      "created": 1.0,
      "id": "lock",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "report",
        "notification",
        "notify"
      ],
      "name": "flag",
      "unicode": "f024",
      "created": 1.0,
      "id": "flag",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "sound",
        "listen",
        "music",
        "audio"
      ],
      "name": "headphones",
      "unicode": "f025",
      "created": 1.0,
      "id": "headphones",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "audio",
        "mute",
        "sound",
        "music"
      ],
      "name": "volume-off",
      "unicode": "f026",
      "created": 1.0,
      "id": "volume-off",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "audio",
        "lower",
        "quieter",
        "sound",
        "music"
      ],
      "name": "volume-down",
      "unicode": "f027",
      "created": 1.0,
      "id": "volume-down",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "audio",
        "higher",
        "louder",
        "sound",
        "music"
      ],
      "name": "volume-up",
      "unicode": "f028",
      "created": 1.0,
      "id": "volume-up",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "scan"
      ],
      "name": "qrcode",
      "unicode": "f029",
      "created": 1.0,
      "id": "qrcode",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "scan"
      ],
      "name": "barcode",
      "unicode": "f02a",
      "created": 1.0,
      "id": "barcode",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "label"
      ],
      "name": "tag",
      "unicode": "f02b",
      "created": 1.0,
      "id": "tag",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "labels"
      ],
      "name": "tags",
      "unicode": "f02c",
      "created": 1.0,
      "id": "tags",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "read",
        "documentation"
      ],
      "name": "book",
      "unicode": "f02d",
      "created": 1.0,
      "id": "book",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "save"
      ],
      "name": "bookmark",
      "unicode": "f02e",
      "created": 1.0,
      "id": "bookmark",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "id": "print",
      "unicode": "f02f",
      "name": "print",
      "categories": [
        "Web Application Icons"
      ],
      "created": 1.0
    },
    {
      "filter": [
        "photo",
        "picture",
        "record"
      ],
      "name": "camera",
      "unicode": "f030",
      "created": 1.0,
      "id": "camera",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "text"
      ],
      "name": "font",
      "unicode": "f031",
      "created": 1.0,
      "id": "font",
      "categories": [
        "Text Editor Icons"
      ]
    },
    {
      "id": "bold",
      "unicode": "f032",
      "name": "bold",
      "categories": [
        "Text Editor Icons"
      ],
      "created": 1.0
    },
    {
      "filter": [
        "italics"
      ],
      "name": "italic",
      "unicode": "f033",
      "created": 1.0,
      "id": "italic",
      "categories": [
        "Text Editor Icons"
      ]
    },
    {
      "id": "text-height",
      "unicode": "f034",
      "name": "text-height",
      "categories": [
        "Text Editor Icons"
      ],
      "created": 1.0
    },
    {
      "id": "text-width",
      "unicode": "f035",
      "name": "text-width",
      "categories": [
        "Text Editor Icons"
      ],
      "created": 1.0
    },
    {
      "filter": [
        "text"
      ],
      "name": "align-left",
      "unicode": "f036",
      "created": 1.0,
      "id": "align-left",
      "categories": [
        "Text Editor Icons"
      ]
    },
    {
      "filter": [
        "middle",
        "text"
      ],
      "name": "align-center",
      "unicode": "f037",
      "created": 1.0,
      "id": "align-center",
      "categories": [
        "Text Editor Icons"
      ]
    },
    {
      "filter": [
        "text"
      ],
      "name": "align-right",
      "unicode": "f038",
      "created": 1.0,
      "id": "align-right",
      "categories": [
        "Text Editor Icons"
      ]
    },
    {
      "filter": [
        "text"
      ],
      "name": "align-justify",
      "unicode": "f039",
      "created": 1.0,
      "id": "align-justify",
      "categories": [
        "Text Editor Icons"
      ]
    },
    {
      "filter": [
        "ul",
        "ol",
        "checklist",
        "finished",
        "completed",
        "done",
        "todo"
      ],
      "name": "list",
      "unicode": "f03a",
      "created": 1.0,
      "id": "list",
      "categories": [
        "Text Editor Icons"
      ]
    },
    {
      "name": "Outdent",
      "unicode": "f03b",
      "created": 1.0,
      "id": "outdent",
      "categories": [
        "Text Editor Icons"
      ],
      "aliases": [
        "dedent"
      ]
    },
    {
      "id": "indent",
      "unicode": "f03c",
      "name": "Indent",
      "categories": [
        "Text Editor Icons"
      ],
      "created": 1.0
    },
    {
      "filter": [
        "film",
        "movie",
        "record"
      ],
      "name": "Video Camera",
      "unicode": "f03d",
      "created": 1.0,
      "id": "video-camera",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "name": "Picture Outlined",
      "unicode": "f03e",
      "created": 1.0,
      "id": "picture-o",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "photo",
        "image"
      ]
    },
    {
      "filter": [
        "write",
        "edit",
        "update"
      ],
      "name": "pencil",
      "unicode": "f040",
      "created": 1.0,
      "id": "pencil",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "map",
        "pin",
        "location",
        "coordinates",
        "localize",
        "address",
        "travel",
        "where",
        "place"
      ],
      "name": "map-marker",
      "unicode": "f041",
      "created": 1.0,
      "id": "map-marker",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "contrast"
      ],
      "name": "adjust",
      "unicode": "f042",
      "created": 1.0,
      "id": "adjust",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "raindrop",
        "waterdrop",
        "drop",
        "droplet"
      ],
      "name": "tint",
      "unicode": "f043",
      "created": 1.0,
      "id": "tint",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "write",
        "edit",
        "update"
      ],
      "name": "Pencil Square Outlined",
      "unicode": "f044",
      "created": 1.0,
      "id": "pencil-square-o",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "edit"
      ]
    },
    {
      "filter": [
        "social",
        "send",
        "arrow"
      ],
      "name": "Share Square Outlined",
      "unicode": "f045",
      "created": 1.0,
      "id": "share-square-o",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "todo",
        "done",
        "agree",
        "accept",
        "confirm",
        "ok"
      ],
      "name": "Check Square Outlined",
      "unicode": "f046",
      "created": 1.0,
      "id": "check-square-o",
      "categories": [
        "Web Application Icons",
        "Form Control Icons"
      ]
    },
    {
      "filter": [
        "move",
        "reorder",
        "resize"
      ],
      "name": "Arrows",
      "unicode": "f047",
      "created": 1.0,
      "id": "arrows",
      "categories": [
        "Web Application Icons",
        "Directional Icons"
      ]
    },
    {
      "filter": [
        "rewind",
        "previous",
        "beginning",
        "start",
        "first"
      ],
      "name": "step-backward",
      "unicode": "f048",
      "created": 1.0,
      "id": "step-backward",
      "categories": [
        "Video Player Icons"
      ]
    },
    {
      "filter": [
        "rewind",
        "previous",
        "beginning",
        "start",
        "first"
      ],
      "name": "fast-backward",
      "unicode": "f049",
      "created": 1.0,
      "id": "fast-backward",
      "categories": [
        "Video Player Icons"
      ]
    },
    {
      "filter": [
        "rewind",
        "previous"
      ],
      "name": "backward",
      "unicode": "f04a",
      "created": 1.0,
      "id": "backward",
      "categories": [
        "Video Player Icons"
      ]
    },
    {
      "filter": [
        "start",
        "playing",
        "music",
        "sound"
      ],
      "name": "play",
      "unicode": "f04b",
      "created": 1.0,
      "id": "play",
      "categories": [
        "Video Player Icons"
      ]
    },
    {
      "filter": [
        "wait"
      ],
      "name": "pause",
      "unicode": "f04c",
      "created": 1.0,
      "id": "pause",
      "categories": [
        "Video Player Icons"
      ]
    },
    {
      "filter": [
        "block",
        "box",
        "square"
      ],
      "name": "stop",
      "unicode": "f04d",
      "created": 1.0,
      "id": "stop",
      "categories": [
        "Video Player Icons"
      ]
    },
    {
      "filter": [
        "forward",
        "next"
      ],
      "name": "forward",
      "unicode": "f04e",
      "created": 1.0,
      "id": "forward",
      "categories": [
        "Video Player Icons"
      ]
    },
    {
      "filter": [
        "next",
        "end",
        "last"
      ],
      "name": "fast-forward",
      "unicode": "f050",
      "created": 1.0,
      "id": "fast-forward",
      "categories": [
        "Video Player Icons"
      ]
    },
    {
      "filter": [
        "next",
        "end",
        "last"
      ],
      "name": "step-forward",
      "unicode": "f051",
      "created": 1.0,
      "id": "step-forward",
      "categories": [
        "Video Player Icons"
      ]
    },
    {
      "id": "eject",
      "unicode": "f052",
      "name": "eject",
      "categories": [
        "Video Player Icons"
      ],
      "created": 1.0
    },
    {
      "filter": [
        "bracket",
        "previous",
        "back"
      ],
      "name": "chevron-left",
      "unicode": "f053",
      "created": 1.0,
      "id": "chevron-left",
      "categories": [
        "Directional Icons"
      ]
    },
    {
      "filter": [
        "bracket",
        "next",
        "forward"
      ],
      "name": "chevron-right",
      "unicode": "f054",
      "created": 1.0,
      "id": "chevron-right",
      "categories": [
        "Directional Icons"
      ]
    },
    {
      "filter": [
        "add",
        "new",
        "create",
        "expand"
      ],
      "name": "Plus Circle",
      "unicode": "f055",
      "created": 1.0,
      "id": "plus-circle",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "delete",
        "remove",
        "trash",
        "hide"
      ],
      "name": "Minus Circle",
      "unicode": "f056",
      "created": 1.0,
      "id": "minus-circle",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "close",
        "exit",
        "x"
      ],
      "name": "Times Circle",
      "unicode": "f057",
      "created": 1.0,
      "id": "times-circle",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "todo",
        "done",
        "agree",
        "accept",
        "confirm",
        "ok"
      ],
      "name": "Check Circle",
      "unicode": "f058",
      "created": 1.0,
      "id": "check-circle",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "help",
        "information",
        "unknown",
        "support"
      ],
      "name": "Question Circle",
      "unicode": "f059",
      "created": 1.0,
      "id": "question-circle",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "help",
        "information",
        "more",
        "details"
      ],
      "name": "Info Circle",
      "unicode": "f05a",
      "created": 1.0,
      "id": "info-circle",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "picker"
      ],
      "name": "Crosshairs",
      "unicode": "f05b",
      "created": 1.0,
      "id": "crosshairs",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "close",
        "exit",
        "x"
      ],
      "name": "Times Circle Outlined",
      "unicode": "f05c",
      "created": 1.0,
      "id": "times-circle-o",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "todo",
        "done",
        "agree",
        "accept",
        "confirm",
        "ok"
      ],
      "name": "Check Circle Outlined",
      "unicode": "f05d",
      "created": 1.0,
      "id": "check-circle-o",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "delete",
        "remove",
        "trash",
        "hide",
        "block",
        "stop",
        "abort",
        "cancel"
      ],
      "name": "ban",
      "unicode": "f05e",
      "created": 1.0,
      "id": "ban",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "previous",
        "back"
      ],
      "name": "arrow-left",
      "unicode": "f060",
      "created": 1.0,
      "id": "arrow-left",
      "categories": [
        "Directional Icons"
      ]
    },
    {
      "filter": [
        "next",
        "forward"
      ],
      "name": "arrow-right",
      "unicode": "f061",
      "created": 1.0,
      "id": "arrow-right",
      "categories": [
        "Directional Icons"
      ]
    },
    {
      "id": "arrow-up",
      "unicode": "f062",
      "name": "arrow-up",
      "categories": [
        "Directional Icons"
      ],
      "created": 1.0
    },
    {
      "filter": [
        "download"
      ],
      "name": "arrow-down",
      "unicode": "f063",
      "created": 1.0,
      "id": "arrow-down",
      "categories": [
        "Directional Icons"
      ]
    },
    {
      "name": "Share",
      "unicode": "f064",
      "created": 1.0,
      "id": "share",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "mail-forward"
      ]
    },
    {
      "filter": [
        "enlarge",
        "bigger",
        "resize"
      ],
      "name": "Expand",
      "unicode": "f065",
      "created": 1.0,
      "id": "expand",
      "categories": [
        "Video Player Icons"
      ]
    },
    {
      "filter": [
        "collapse",
        "combine",
        "contract",
        "merge",
        "smaller"
      ],
      "name": "Compress",
      "unicode": "f066",
      "created": 1.0,
      "id": "compress",
      "categories": [
        "Video Player Icons"
      ]
    },
    {
      "filter": [
        "add",
        "new",
        "create",
        "expand"
      ],
      "name": "plus",
      "unicode": "f067",
      "created": 1.0,
      "id": "plus",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "hide",
        "minify",
        "delete",
        "remove",
        "trash",
        "hide",
        "collapse"
      ],
      "name": "minus",
      "unicode": "f068",
      "created": 1.0,
      "id": "minus",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "details"
      ],
      "name": "asterisk",
      "unicode": "f069",
      "created": 1.0,
      "id": "asterisk",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "warning",
        "error",
        "problem",
        "notification",
        "alert"
      ],
      "name": "Exclamation Circle",
      "unicode": "f06a",
      "created": 1.0,
      "id": "exclamation-circle",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "present"
      ],
      "name": "gift",
      "unicode": "f06b",
      "created": 1.0,
      "id": "gift",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "eco",
        "nature",
        "plant"
      ],
      "name": "leaf",
      "unicode": "f06c",
      "created": 1.0,
      "id": "leaf",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "flame",
        "hot",
        "popular"
      ],
      "name": "fire",
      "unicode": "f06d",
      "created": 1.0,
      "id": "fire",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "show",
        "visible",
        "views"
      ],
      "name": "Eye",
      "unicode": "f06e",
      "created": 1.0,
      "id": "eye",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "toggle",
        "show",
        "hide",
        "visible",
        "visiblity",
        "views"
      ],
      "name": "Eye Slash",
      "unicode": "f070",
      "created": 1.0,
      "id": "eye-slash",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "warning",
        "error",
        "problem",
        "notification",
        "alert"
      ],
      "name": "Exclamation Triangle",
      "unicode": "f071",
      "created": 1.0,
      "id": "exclamation-triangle",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "warning"
      ]
    },
    {
      "filter": [
        "travel",
        "trip",
        "location",
        "destination",
        "airplane",
        "fly",
        "mode"
      ],
      "name": "plane",
      "unicode": "f072",
      "created": 1.0,
      "id": "plane",
      "categories": [
        "Web Application Icons",
        "Transportation Icons"
      ]
    },
    {
      "filter": [
        "date",
        "time",
        "when",
        "event"
      ],
      "name": "calendar",
      "unicode": "f073",
      "created": 1.0,
      "id": "calendar",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "sort",
        "shuffle"
      ],
      "name": "random",
      "unicode": "f074",
      "created": 1.0,
      "id": "random",
      "categories": [
        "Web Application Icons",
        "Video Player Icons"
      ]
    },
    {
      "filter": [
        "speech",
        "notification",
        "note",
        "chat",
        "bubble",
        "feedback",
        "message",
        "texting",
        "sms",
        "conversation"
      ],
      "name": "comment",
      "unicode": "f075",
      "created": 1.0,
      "id": "comment",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "id": "magnet",
      "unicode": "f076",
      "name": "magnet",
      "categories": [
        "Web Application Icons"
      ],
      "created": 1.0
    },
    {
      "id": "chevron-up",
      "unicode": "f077",
      "name": "chevron-up",
      "categories": [
        "Directional Icons"
      ],
      "created": 1.0
    },
    {
      "id": "chevron-down",
      "unicode": "f078",
      "name": "chevron-down",
      "categories": [
        "Directional Icons"
      ],
      "created": 1.0
    },
    {
      "filter": [
        "refresh",
        "reload",
        "share"
      ],
      "name": "retweet",
      "unicode": "f079",
      "created": 1.0,
      "id": "retweet",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "checkout",
        "buy",
        "purchase",
        "payment"
      ],
      "name": "shopping-cart",
      "unicode": "f07a",
      "created": 1.0,
      "id": "shopping-cart",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "id": "folder",
      "unicode": "f07b",
      "name": "Folder",
      "categories": [
        "Web Application Icons"
      ],
      "created": 1.0
    },
    {
      "id": "folder-open",
      "unicode": "f07c",
      "name": "Folder Open",
      "categories": [
        "Web Application Icons"
      ],
      "created": 1.0
    },
    {
      "filter": [
        "resize"
      ],
      "name": "Arrows Vertical",
      "unicode": "f07d",
      "created": 1.0,
      "id": "arrows-v",
      "categories": [
        "Web Application Icons",
        "Directional Icons"
      ]
    },
    {
      "filter": [
        "resize"
      ],
      "name": "Arrows Horizontal",
      "unicode": "f07e",
      "created": 1.0,
      "id": "arrows-h",
      "categories": [
        "Web Application Icons",
        "Directional Icons"
      ]
    },
    {
      "filter": [
        "graph",
        "analytics"
      ],
      "name": "Bar Chart",
      "unicode": "f080",
      "created": 1.0,
      "id": "bar-chart",
      "categories": [
        "Web Application Icons",
        "Chart Icons"
      ],
      "aliases": [
        "bar-chart-o"
      ]
    },
    {
      "filter": [
        "tweet",
        "social network"
      ],
      "name": "Twitter Square",
      "unicode": "f081",
      "created": 1.0,
      "id": "twitter-square",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "filter": [
        "social network"
      ],
      "name": "Facebook Square",
      "unicode": "f082",
      "created": 1.0,
      "id": "facebook-square",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "filter": [
        "photo",
        "picture",
        "record"
      ],
      "name": "camera-retro",
      "unicode": "f083",
      "created": 1.0,
      "id": "camera-retro",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "unlock",
        "password"
      ],
      "name": "key",
      "unicode": "f084",
      "created": 1.0,
      "id": "key",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "settings"
      ],
      "name": "cogs",
      "unicode": "f085",
      "created": 1.0,
      "id": "cogs",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "gears"
      ]
    },
    {
      "filter": [
        "speech",
        "notification",
        "note",
        "chat",
        "bubble",
        "feedback",
        "message",
        "texting",
        "sms",
        "conversation"
      ],
      "name": "comments",
      "unicode": "f086",
      "created": 1.0,
      "id": "comments",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "like",
        "approve",
        "favorite",
        "agree",
        "hand"
      ],
      "name": "Thumbs Up Outlined",
      "unicode": "f087",
      "created": 1.0,
      "id": "thumbs-o-up",
      "categories": [
        "Web Application Icons",
        "Hand Icons"
      ]
    },
    {
      "filter": [
        "dislike",
        "disapprove",
        "disagree",
        "hand"
      ],
      "name": "Thumbs Down Outlined",
      "unicode": "f088",
      "created": 1.0,
      "id": "thumbs-o-down",
      "categories": [
        "Web Application Icons",
        "Hand Icons"
      ]
    },
    {
      "filter": [
        "award",
        "achievement",
        "rating",
        "score"
      ],
      "name": "star-half",
      "unicode": "f089",
      "created": 1.0,
      "id": "star-half",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "love",
        "like",
        "favorite"
      ],
      "name": "Heart Outlined",
      "unicode": "f08a",
      "created": 1.0,
      "id": "heart-o",
      "categories": [
        "Web Application Icons",
        "Medical Icons"
      ]
    },
    {
      "filter": [
        "log out",
        "logout",
        "leave",
        "exit",
        "arrow"
      ],
      "name": "Sign Out",
      "unicode": "f08b",
      "created": 1.0,
      "id": "sign-out",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "id": "linkedin-square",
      "unicode": "f08c",
      "name": "LinkedIn Square",
      "categories": [
        "Brand Icons"
      ],
      "created": 1.0
    },
    {
      "filter": [
        "marker",
        "pin",
        "location",
        "coordinates"
      ],
      "name": "Thumb Tack",
      "unicode": "f08d",
      "created": 1.0,
      "id": "thumb-tack",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "open",
        "new"
      ],
      "name": "External Link",
      "unicode": "f08e",
      "created": 1.0,
      "id": "external-link",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "enter",
        "join",
        "log in",
        "login",
        "sign up",
        "sign in",
        "signin",
        "signup",
        "arrow"
      ],
      "name": "Sign In",
      "unicode": "f090",
      "created": 1.0,
      "id": "sign-in",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "award",
        "achievement",
        "cup",
        "winner",
        "game"
      ],
      "name": "trophy",
      "unicode": "f091",
      "created": 1.0,
      "id": "trophy",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "octocat"
      ],
      "name": "GitHub Square",
      "unicode": "f092",
      "created": 1.0,
      "url": "github.com/logos",
      "id": "github-square",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "filter": [
        "import"
      ],
      "name": "Upload",
      "unicode": "f093",
      "created": 1.0,
      "id": "upload",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "food"
      ],
      "name": "Lemon Outlined",
      "unicode": "f094",
      "created": 1.0,
      "id": "lemon-o",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "call",
        "voice",
        "number",
        "support",
        "earphone",
        "telephone"
      ],
      "name": "Phone",
      "unicode": "f095",
      "created": 2.0,
      "id": "phone",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "block",
        "square",
        "box"
      ],
      "name": "Square Outlined",
      "unicode": "f096",
      "created": 2.0,
      "id": "square-o",
      "categories": [
        "Web Application Icons",
        "Form Control Icons"
      ]
    },
    {
      "filter": [
        "save"
      ],
      "name": "Bookmark Outlined",
      "unicode": "f097",
      "created": 2.0,
      "id": "bookmark-o",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "call",
        "voice",
        "number",
        "support",
        "telephone"
      ],
      "name": "Phone Square",
      "unicode": "f098",
      "created": 2.0,
      "id": "phone-square",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "tweet",
        "social network"
      ],
      "name": "Twitter",
      "unicode": "f099",
      "created": 2.0,
      "id": "twitter",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "filter": [
        "social network"
      ],
      "name": "Facebook",
      "unicode": "f09a",
      "created": 2.0,
      "id": "facebook",
      "categories": [
        "Brand Icons"
      ],
      "aliases": [
        "facebook-f"
      ]
    },
    {
      "filter": [
        "octocat"
      ],
      "name": "GitHub",
      "unicode": "f09b",
      "created": 2.0,
      "url": "github.com/logos",
      "id": "github",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "filter": [
        "protect",
        "admin",
        "password",
        "lock"
      ],
      "name": "unlock",
      "unicode": "f09c",
      "created": 2.0,
      "id": "unlock",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "money",
        "buy",
        "debit",
        "checkout",
        "purchase",
        "payment"
      ],
      "name": "credit-card",
      "unicode": "f09d",
      "created": 2.0,
      "id": "credit-card",
      "categories": [
        "Web Application Icons",
        "Payment Icons"
      ]
    },
    {
      "filter": [
        "blog"
      ],
      "name": "rss",
      "unicode": "f09e",
      "created": 2.0,
      "id": "rss",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "feed"
      ]
    },
    {
      "filter": [
        "harddrive",
        "hard drive",
        "storage",
        "save"
      ],
      "name": "HDD",
      "unicode": "f0a0",
      "created": 2.0,
      "id": "hdd-o",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "announcement",
        "share",
        "broadcast",
        "louder",
        "megaphone"
      ],
      "name": "bullhorn",
      "unicode": "f0a1",
      "created": 2.0,
      "id": "bullhorn",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "alert",
        "reminder",
        "notification"
      ],
      "name": "bell",
      "unicode": "f0f3",
      "created": 2.0,
      "id": "bell",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "badge",
        "star"
      ],
      "name": "certificate",
      "unicode": "f0a3",
      "created": 2.0,
      "id": "certificate",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "point",
        "right",
        "next",
        "forward",
        "finger"
      ],
      "name": "Hand Outlined Right",
      "unicode": "f0a4",
      "created": 2.0,
      "id": "hand-o-right",
      "categories": [
        "Directional Icons",
        "Hand Icons"
      ]
    },
    {
      "filter": [
        "point",
        "left",
        "previous",
        "back",
        "finger"
      ],
      "name": "Hand Outlined Left",
      "unicode": "f0a5",
      "created": 2.0,
      "id": "hand-o-left",
      "categories": [
        "Directional Icons",
        "Hand Icons"
      ]
    },
    {
      "filter": [
        "point",
        "finger"
      ],
      "name": "Hand Outlined Up",
      "unicode": "f0a6",
      "created": 2.0,
      "id": "hand-o-up",
      "categories": [
        "Directional Icons",
        "Hand Icons"
      ]
    },
    {
      "filter": [
        "point",
        "finger"
      ],
      "name": "Hand Outlined Down",
      "unicode": "f0a7",
      "created": 2.0,
      "id": "hand-o-down",
      "categories": [
        "Directional Icons",
        "Hand Icons"
      ]
    },
    {
      "filter": [
        "previous",
        "back"
      ],
      "name": "Arrow Circle Left",
      "unicode": "f0a8",
      "created": 2.0,
      "id": "arrow-circle-left",
      "categories": [
        "Directional Icons"
      ]
    },
    {
      "filter": [
        "next",
        "forward"
      ],
      "name": "Arrow Circle Right",
      "unicode": "f0a9",
      "created": 2.0,
      "id": "arrow-circle-right",
      "categories": [
        "Directional Icons"
      ]
    },
    {
      "id": "arrow-circle-up",
      "unicode": "f0aa",
      "name": "Arrow Circle Up",
      "categories": [
        "Directional Icons"
      ],
      "created": 2.0
    },
    {
      "filter": [
        "download"
      ],
      "name": "Arrow Circle Down",
      "unicode": "f0ab",
      "created": 2.0,
      "id": "arrow-circle-down",
      "categories": [
        "Directional Icons"
      ]
    },
    {
      "filter": [
        "world",
        "planet",
        "map",
        "place",
        "travel",
        "earth",
        "global",
        "translate",
        "all",
        "language",
        "localize",
        "location",
        "coordinates",
        "country"
      ],
      "name": "Globe",
      "unicode": "f0ac",
      "created": 2.0,
      "id": "globe",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "settings",
        "fix",
        "update",
        "spanner"
      ],
      "name": "Wrench",
      "unicode": "f0ad",
      "created": 2.0,
      "id": "wrench",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "progress",
        "loading",
        "downloading",
        "downloads",
        "settings"
      ],
      "name": "Tasks",
      "unicode": "f0ae",
      "created": 2.0,
      "id": "tasks",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "funnel",
        "options"
      ],
      "name": "Filter",
      "unicode": "f0b0",
      "created": 2.0,
      "id": "filter",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "work",
        "business",
        "office",
        "luggage",
        "bag"
      ],
      "name": "Briefcase",
      "unicode": "f0b1",
      "created": 2.0,
      "id": "briefcase",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "expand",
        "enlarge",
        "fullscreen",
        "bigger",
        "move",
        "reorder",
        "resize",
        "arrow"
      ],
      "name": "Arrows Alt",
      "unicode": "f0b2",
      "created": 2.0,
      "id": "arrows-alt",
      "categories": [
        "Video Player Icons",
        "Directional Icons"
      ]
    },
    {
      "filter": [
        "people",
        "profiles",
        "persons"
      ],
      "name": "Users",
      "unicode": "f0c0",
      "created": 2.0,
      "id": "users",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "group"
      ]
    },
    {
      "filter": [
        "chain"
      ],
      "name": "Link",
      "unicode": "f0c1",
      "created": 2.0,
      "id": "link",
      "categories": [
        "Text Editor Icons"
      ],
      "aliases": [
        "chain"
      ]
    },
    {
      "filter": [
        "save"
      ],
      "name": "Cloud",
      "unicode": "f0c2",
      "created": 2.0,
      "id": "cloud",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "science",
        "beaker",
        "experimental",
        "labs"
      ],
      "name": "Flask",
      "unicode": "f0c3",
      "created": 2.0,
      "id": "flask",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "name": "Scissors",
      "unicode": "f0c4",
      "created": 2.0,
      "id": "scissors",
      "categories": [
        "Text Editor Icons"
      ],
      "aliases": [
        "cut"
      ]
    },
    {
      "filter": [
        "duplicate",
        "clone",
        "copy"
      ],
      "name": "Files Outlined",
      "unicode": "f0c5",
      "created": 2.0,
      "id": "files-o",
      "categories": [
        "Text Editor Icons"
      ],
      "aliases": [
        "copy"
      ]
    },
    {
      "filter": [
        "attachment"
      ],
      "name": "Paperclip",
      "unicode": "f0c6",
      "created": 2.0,
      "id": "paperclip",
      "categories": [
        "Text Editor Icons"
      ]
    },
    {
      "name": "Floppy Outlined",
      "unicode": "f0c7",
      "created": 2.0,
      "id": "floppy-o",
      "categories": [
        "Text Editor Icons"
      ],
      "aliases": [
        "save"
      ]
    },
    {
      "filter": [
        "block",
        "box"
      ],
      "name": "Square",
      "unicode": "f0c8",
      "created": 2.0,
      "id": "square",
      "categories": [
        "Web Application Icons",
        "Form Control Icons"
      ]
    },
    {
      "filter": [
        "menu",
        "drag",
        "reorder",
        "settings",
        "list",
        "ul",
        "ol",
        "checklist",
        "todo",
        "list",
        "hamburger"
      ],
      "name": "Bars",
      "unicode": "f0c9",
      "created": 2.0,
      "id": "bars",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "navicon",
        "reorder"
      ]
    },
    {
      "filter": [
        "ul",
        "ol",
        "checklist",
        "todo",
        "list"
      ],
      "name": "list-ul",
      "unicode": "f0ca",
      "created": 2.0,
      "id": "list-ul",
      "categories": [
        "Text Editor Icons"
      ]
    },
    {
      "filter": [
        "ul",
        "ol",
        "checklist",
        "list",
        "todo",
        "list",
        "numbers"
      ],
      "name": "list-ol",
      "unicode": "f0cb",
      "created": 2.0,
      "id": "list-ol",
      "categories": [
        "Text Editor Icons"
      ]
    },
    {
      "id": "strikethrough",
      "unicode": "f0cc",
      "name": "Strikethrough",
      "categories": [
        "Text Editor Icons"
      ],
      "created": 2.0
    },
    {
      "id": "underline",
      "unicode": "f0cd",
      "name": "Underline",
      "categories": [
        "Text Editor Icons"
      ],
      "created": 2.0
    },
    {
      "filter": [
        "data",
        "excel",
        "spreadsheet"
      ],
      "name": "table",
      "unicode": "f0ce",
      "created": 2.0,
      "id": "table",
      "categories": [
        "Text Editor Icons"
      ]
    },
    {
      "filter": [
        "wizard",
        "automatic",
        "autocomplete"
      ],
      "name": "magic",
      "unicode": "f0d0",
      "created": 2.0,
      "id": "magic",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "shipping"
      ],
      "name": "truck",
      "unicode": "f0d1",
      "created": 2.0,
      "id": "truck",
      "categories": [
        "Web Application Icons",
        "Transportation Icons"
      ]
    },
    {
      "id": "pinterest",
      "unicode": "f0d2",
      "name": "Pinterest",
      "categories": [
        "Brand Icons"
      ],
      "created": 2.0
    },
    {
      "id": "pinterest-square",
      "unicode": "f0d3",
      "name": "Pinterest Square",
      "categories": [
        "Brand Icons"
      ],
      "created": 2.0
    },
    {
      "filter": [
        "social network"
      ],
      "name": "Google Plus Square",
      "unicode": "f0d4",
      "created": 2.0,
      "id": "google-plus-square",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "filter": [
        "social network"
      ],
      "name": "Google Plus",
      "unicode": "f0d5",
      "created": 2.0,
      "id": "google-plus",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "filter": [
        "cash",
        "money",
        "buy",
        "checkout",
        "purchase",
        "payment"
      ],
      "name": "Money",
      "unicode": "f0d6",
      "created": 2.0,
      "id": "money",
      "categories": [
        "Web Application Icons",
        "Currency Icons"
      ]
    },
    {
      "filter": [
        "more",
        "dropdown",
        "menu",
        "triangle down",
        "arrow"
      ],
      "name": "Caret Down",
      "unicode": "f0d7",
      "created": 2.0,
      "id": "caret-down",
      "categories": [
        "Directional Icons"
      ]
    },
    {
      "filter": [
        "triangle up",
        "arrow"
      ],
      "name": "Caret Up",
      "unicode": "f0d8",
      "created": 2.0,
      "id": "caret-up",
      "categories": [
        "Directional Icons"
      ]
    },
    {
      "filter": [
        "previous",
        "back",
        "triangle left",
        "arrow"
      ],
      "name": "Caret Left",
      "unicode": "f0d9",
      "created": 2.0,
      "id": "caret-left",
      "categories": [
        "Directional Icons"
      ]
    },
    {
      "filter": [
        "next",
        "forward",
        "triangle right",
        "arrow"
      ],
      "name": "Caret Right",
      "unicode": "f0da",
      "created": 2.0,
      "id": "caret-right",
      "categories": [
        "Directional Icons"
      ]
    },
    {
      "filter": [
        "split",
        "panes"
      ],
      "name": "Columns",
      "unicode": "f0db",
      "created": 2.0,
      "id": "columns",
      "categories": [
        "Text Editor Icons"
      ]
    },
    {
      "filter": [
        "order"
      ],
      "name": "Sort",
      "unicode": "f0dc",
      "created": 2.0,
      "id": "sort",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "unsorted"
      ]
    },
    {
      "filter": [
        "dropdown",
        "more",
        "menu",
        "arrow"
      ],
      "name": "Sort Descending",
      "unicode": "f0dd",
      "created": 2.0,
      "id": "sort-desc",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "sort-down"
      ]
    },
    {
      "filter": [
        "arrow"
      ],
      "name": "Sort Ascending",
      "unicode": "f0de",
      "created": 2.0,
      "id": "sort-asc",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "sort-up"
      ]
    },
    {
      "filter": [
        "email",
        "e-mail",
        "letter",
        "support",
        "mail",
        "notification"
      ],
      "name": "Envelope",
      "unicode": "f0e0",
      "created": 2.0,
      "id": "envelope",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "id": "linkedin",
      "unicode": "f0e1",
      "name": "LinkedIn",
      "categories": [
        "Brand Icons"
      ],
      "created": 2.0
    },
    {
      "filter": [
        "back"
      ],
      "name": "Undo",
      "unicode": "f0e2",
      "created": 2.0,
      "id": "undo",
      "categories": [
        "Text Editor Icons"
      ],
      "aliases": [
        "rotate-left"
      ]
    },
    {
      "filter": [
        "judge",
        "lawyer",
        "opinion"
      ],
      "name": "Gavel",
      "unicode": "f0e3",
      "created": 2.0,
      "id": "gavel",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "legal"
      ]
    },
    {
      "filter": [
        "speedometer",
        "fast"
      ],
      "name": "Tachometer",
      "unicode": "f0e4",
      "created": 2.0,
      "id": "tachometer",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "dashboard"
      ]
    },
    {
      "filter": [
        "speech",
        "notification",
        "note",
        "chat",
        "bubble",
        "feedback",
        "message",
        "texting",
        "sms",
        "conversation"
      ],
      "name": "comment-o",
      "unicode": "f0e5",
      "created": 2.0,
      "id": "comment-o",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "speech",
        "notification",
        "note",
        "chat",
        "bubble",
        "feedback",
        "message",
        "texting",
        "sms",
        "conversation"
      ],
      "name": "comments-o",
      "unicode": "f0e6",
      "created": 2.0,
      "id": "comments-o",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "lightning",
        "weather"
      ],
      "name": "Lightning Bolt",
      "unicode": "f0e7",
      "created": 2.0,
      "id": "bolt",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "flash"
      ]
    },
    {
      "filter": [
        "directory",
        "hierarchy",
        "organization"
      ],
      "name": "Sitemap",
      "unicode": "f0e8",
      "created": 2.0,
      "id": "sitemap",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "id": "umbrella",
      "unicode": "f0e9",
      "name": "Umbrella",
      "categories": [
        "Web Application Icons"
      ],
      "created": 2.0
    },
    {
      "filter": [
        "copy"
      ],
      "name": "Clipboard",
      "unicode": "f0ea",
      "created": 2.0,
      "id": "clipboard",
      "categories": [
        "Text Editor Icons"
      ],
      "aliases": [
        "paste"
      ]
    },
    {
      "filter": [
        "idea",
        "inspiration"
      ],
      "name": "Lightbulb Outlined",
      "unicode": "f0eb",
      "created": 3.0,
      "id": "lightbulb-o",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "transfer",
        "arrows",
        "arrow"
      ],
      "name": "Exchange",
      "unicode": "f0ec",
      "created": 3.0,
      "id": "exchange",
      "categories": [
        "Web Application Icons",
        "Directional Icons"
      ]
    },
    {
      "filter": [
        "import"
      ],
      "name": "Cloud Download",
      "unicode": "f0ed",
      "created": 3.0,
      "id": "cloud-download",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "import"
      ],
      "name": "Cloud Upload",
      "unicode": "f0ee",
      "created": 3.0,
      "id": "cloud-upload",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "doctor",
        "profile",
        "medical",
        "nurse"
      ],
      "name": "user-md",
      "unicode": "f0f0",
      "created": 2.0,
      "id": "user-md",
      "categories": [
        "Medical Icons"
      ]
    },
    {
      "id": "stethoscope",
      "unicode": "f0f1",
      "name": "Stethoscope",
      "categories": [
        "Medical Icons"
      ],
      "created": 3.0
    },
    {
      "filter": [
        "trip",
        "luggage",
        "travel",
        "move",
        "baggage"
      ],
      "name": "Suitcase",
      "unicode": "f0f2",
      "created": 3.0,
      "id": "suitcase",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "alert",
        "reminder",
        "notification"
      ],
      "name": "Bell Outlined",
      "unicode": "f0a2",
      "created": 3.0,
      "id": "bell-o",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "morning",
        "mug",
        "breakfast",
        "tea",
        "drink",
        "cafe"
      ],
      "name": "Coffee",
      "unicode": "f0f4",
      "created": 3.0,
      "id": "coffee",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "food",
        "restaurant",
        "spoon",
        "knife",
        "dinner",
        "eat"
      ],
      "name": "Cutlery",
      "unicode": "f0f5",
      "created": 3.0,
      "id": "cutlery",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "new",
        "page",
        "pdf",
        "document"
      ],
      "name": "File Text Outlined",
      "unicode": "f0f6",
      "created": 3.0,
      "id": "file-text-o",
      "categories": [
        "Text Editor Icons",
        "File Type Icons"
      ]
    },
    {
      "filter": [
        "work",
        "business",
        "apartment",
        "office",
        "company"
      ],
      "name": "Building Outlined",
      "unicode": "f0f7",
      "created": 3.0,
      "id": "building-o",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "building"
      ],
      "name": "hospital Outlined",
      "unicode": "f0f8",
      "created": 3.0,
      "id": "hospital-o",
      "categories": [
        "Medical Icons"
      ]
    },
    {
      "filter": [
        "vehicle",
        "support",
        "help"
      ],
      "name": "ambulance",
      "unicode": "f0f9",
      "created": 3.0,
      "id": "ambulance",
      "categories": [
        "Medical Icons",
        "Transportation Icons"
      ]
    },
    {
      "filter": [
        "first aid",
        "firstaid",
        "help",
        "support",
        "health"
      ],
      "name": "medkit",
      "unicode": "f0fa",
      "created": 3.0,
      "id": "medkit",
      "categories": [
        "Medical Icons"
      ]
    },
    {
      "filter": [
        "fly",
        "plane",
        "airplane",
        "quick",
        "fast",
        "travel"
      ],
      "name": "fighter-jet",
      "unicode": "f0fb",
      "created": 3.0,
      "id": "fighter-jet",
      "categories": [
        "Web Application Icons",
        "Transportation Icons"
      ]
    },
    {
      "filter": [
        "alcohol",
        "stein",
        "drink",
        "mug",
        "bar",
        "liquor"
      ],
      "name": "beer",
      "unicode": "f0fc",
      "created": 3.0,
      "id": "beer",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "hospital",
        "hotel"
      ],
      "name": "H Square",
      "unicode": "f0fd",
      "created": 3.0,
      "id": "h-square",
      "categories": [
        "Medical Icons"
      ]
    },
    {
      "filter": [
        "add",
        "new",
        "create",
        "expand"
      ],
      "name": "Plus Square",
      "unicode": "f0fe",
      "created": 3.0,
      "id": "plus-square",
      "categories": [
        "Medical Icons",
        "Web Application Icons",
        "Form Control Icons"
      ]
    },
    {
      "filter": [
        "laquo",
        "quote",
        "previous",
        "back",
        "arrows"
      ],
      "name": "Angle Double Left",
      "unicode": "f100",
      "created": 3.0,
      "id": "angle-double-left",
      "categories": [
        "Directional Icons"
      ]
    },
    {
      "filter": [
        "raquo",
        "quote",
        "next",
        "forward",
        "arrows"
      ],
      "name": "Angle Double Right",
      "unicode": "f101",
      "created": 3.0,
      "id": "angle-double-right",
      "categories": [
        "Directional Icons"
      ]
    },
    {
      "filter": [
        "arrows"
      ],
      "name": "Angle Double Up",
      "unicode": "f102",
      "created": 3.0,
      "id": "angle-double-up",
      "categories": [
        "Directional Icons"
      ]
    },
    {
      "filter": [
        "arrows"
      ],
      "name": "Angle Double Down",
      "unicode": "f103",
      "created": 3.0,
      "id": "angle-double-down",
      "categories": [
        "Directional Icons"
      ]
    },
    {
      "filter": [
        "previous",
        "back",
        "arrow"
      ],
      "name": "angle-left",
      "unicode": "f104",
      "created": 3.0,
      "id": "angle-left",
      "categories": [
        "Directional Icons"
      ]
    },
    {
      "filter": [
        "next",
        "forward",
        "arrow"
      ],
      "name": "angle-right",
      "unicode": "f105",
      "created": 3.0,
      "id": "angle-right",
      "categories": [
        "Directional Icons"
      ]
    },
    {
      "filter": [
        "arrow"
      ],
      "name": "angle-up",
      "unicode": "f106",
      "created": 3.0,
      "id": "angle-up",
      "categories": [
        "Directional Icons"
      ]
    },
    {
      "filter": [
        "arrow"
      ],
      "name": "angle-down",
      "unicode": "f107",
      "created": 3.0,
      "id": "angle-down",
      "categories": [
        "Directional Icons"
      ]
    },
    {
      "filter": [
        "monitor",
        "screen",
        "desktop",
        "computer",
        "demo",
        "device"
      ],
      "name": "Desktop",
      "unicode": "f108",
      "created": 3.0,
      "id": "desktop",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "demo",
        "computer",
        "device"
      ],
      "name": "Laptop",
      "unicode": "f109",
      "created": 3.0,
      "id": "laptop",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "ipad",
        "device"
      ],
      "name": "tablet",
      "unicode": "f10a",
      "created": 3.0,
      "id": "tablet",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "cell phone",
        "cellphone",
        "text",
        "call",
        "iphone",
        "number",
        "telephone"
      ],
      "name": "Mobile Phone",
      "unicode": "f10b",
      "created": 3.0,
      "id": "mobile",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "mobile-phone"
      ]
    },
    {
      "id": "circle-o",
      "unicode": "f10c",
      "name": "Circle Outlined",
      "categories": [
        "Web Application Icons",
        "Form Control Icons"
      ],
      "created": 3.0
    },
    {
      "id": "quote-left",
      "unicode": "f10d",
      "name": "quote-left",
      "categories": [
        "Web Application Icons"
      ],
      "created": 3.0
    },
    {
      "id": "quote-right",
      "unicode": "f10e",
      "name": "quote-right",
      "categories": [
        "Web Application Icons"
      ],
      "created": 3.0
    },
    {
      "filter": [
        "loading",
        "progress"
      ],
      "name": "Spinner",
      "unicode": "f110",
      "created": 3.0,
      "id": "spinner",
      "categories": [
        "Web Application Icons",
        "Spinner Icons"
      ]
    },
    {
      "filter": [
        "dot",
        "notification"
      ],
      "name": "Circle",
      "unicode": "f111",
      "created": 3.0,
      "id": "circle",
      "categories": [
        "Web Application Icons",
        "Form Control Icons"
      ]
    },
    {
      "name": "Reply",
      "unicode": "f112",
      "created": 3.0,
      "id": "reply",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "mail-reply"
      ]
    },
    {
      "filter": [
        "octocat"
      ],
      "name": "GitHub Alt",
      "unicode": "f113",
      "created": 3.0,
      "url": "github.com/logos",
      "id": "github-alt",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "id": "folder-o",
      "unicode": "f114",
      "name": "Folder Outlined",
      "categories": [
        "Web Application Icons"
      ],
      "created": 3.0
    },
    {
      "id": "folder-open-o",
      "unicode": "f115",
      "name": "Folder Open Outlined",
      "categories": [
        "Web Application Icons"
      ],
      "created": 3.0
    },
    {
      "filter": [
        "face",
        "emoticon",
        "happy",
        "approve",
        "satisfied",
        "rating"
      ],
      "name": "Smile Outlined",
      "unicode": "f118",
      "created": 3.1000000000000001,
      "id": "smile-o",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "face",
        "emoticon",
        "sad",
        "disapprove",
        "rating"
      ],
      "name": "Frown Outlined",
      "unicode": "f119",
      "created": 3.1000000000000001,
      "id": "frown-o",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "face",
        "emoticon",
        "rating",
        "neutral"
      ],
      "name": "Meh Outlined",
      "unicode": "f11a",
      "created": 3.1000000000000001,
      "id": "meh-o",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "controller"
      ],
      "name": "Gamepad",
      "unicode": "f11b",
      "created": 3.1000000000000001,
      "id": "gamepad",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "type",
        "input"
      ],
      "name": "Keyboard Outlined",
      "unicode": "f11c",
      "created": 3.1000000000000001,
      "id": "keyboard-o",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "report",
        "notification"
      ],
      "name": "Flag Outlined",
      "unicode": "f11d",
      "created": 3.1000000000000001,
      "id": "flag-o",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "report",
        "notification",
        "notify"
      ],
      "name": "flag-checkered",
      "unicode": "f11e",
      "created": 3.1000000000000001,
      "id": "flag-checkered",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "command",
        "prompt",
        "code"
      ],
      "name": "Terminal",
      "unicode": "f120",
      "created": 3.1000000000000001,
      "id": "terminal",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "html",
        "brackets"
      ],
      "name": "Code",
      "unicode": "f121",
      "created": 3.1000000000000001,
      "id": "code",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "name": "reply-all",
      "unicode": "f122",
      "created": 3.1000000000000001,
      "id": "reply-all",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "mail-reply-all"
      ]
    },
    {
      "filter": [
        "award",
        "achievement",
        "rating",
        "score"
      ],
      "name": "Star Half Outlined",
      "unicode": "f123",
      "created": 3.1000000000000001,
      "id": "star-half-o",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "star-half-empty",
        "star-half-full"
      ]
    },
    {
      "filter": [
        "map",
        "coordinates",
        "location",
        "address",
        "place",
        "where"
      ],
      "name": "location-arrow",
      "unicode": "f124",
      "created": 3.1000000000000001,
      "id": "location-arrow",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "id": "crop",
      "unicode": "f125",
      "name": "crop",
      "categories": [
        "Web Application Icons"
      ],
      "created": 3.1000000000000001
    },
    {
      "filter": [
        "git",
        "fork",
        "vcs",
        "svn",
        "github",
        "rebase",
        "version",
        "merge"
      ],
      "name": "code-fork",
      "unicode": "f126",
      "created": 3.1000000000000001,
      "id": "code-fork",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "remove"
      ],
      "name": "Chain Broken",
      "unicode": "f127",
      "created": 3.1000000000000001,
      "id": "chain-broken",
      "categories": [
        "Text Editor Icons"
      ],
      "aliases": [
        "unlink"
      ]
    },
    {
      "filter": [
        "help",
        "information",
        "unknown",
        "support"
      ],
      "name": "Question",
      "unicode": "f128",
      "created": 3.1000000000000001,
      "id": "question",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "help",
        "information",
        "more",
        "details"
      ],
      "name": "Info",
      "unicode": "f129",
      "created": 3.1000000000000001,
      "id": "info",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "warning",
        "error",
        "problem",
        "notification",
        "notify",
        "alert"
      ],
      "name": "exclamation",
      "unicode": "f12a",
      "created": 3.1000000000000001,
      "id": "exclamation",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "exponential"
      ],
      "name": "superscript",
      "unicode": "f12b",
      "created": 3.1000000000000001,
      "id": "superscript",
      "categories": [
        "Text Editor Icons"
      ]
    },
    {
      "id": "subscript",
      "unicode": "f12c",
      "name": "subscript",
      "categories": [
        "Text Editor Icons"
      ],
      "created": 3.1000000000000001
    },
    {
      "filter": [
        "remove",
        "delete"
      ],
      "name": "eraser",
      "unicode": "f12d",
      "created": 3.1000000000000001,
      "id": "eraser",
      "categories": [
        "Text Editor Icons",
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "addon",
        "add-on",
        "section"
      ],
      "name": "Puzzle Piece",
      "unicode": "f12e",
      "created": 3.1000000000000001,
      "id": "puzzle-piece",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "record",
        "voice",
        "sound"
      ],
      "name": "microphone",
      "unicode": "f130",
      "created": 3.1000000000000001,
      "id": "microphone",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "record",
        "voice",
        "sound",
        "mute"
      ],
      "name": "Microphone Slash",
      "unicode": "f131",
      "created": 3.1000000000000001,
      "id": "microphone-slash",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "award",
        "achievement",
        "security",
        "winner"
      ],
      "name": "shield",
      "unicode": "f132",
      "created": 3.1000000000000001,
      "id": "shield",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "date",
        "time",
        "when",
        "event"
      ],
      "name": "calendar-o",
      "unicode": "f133",
      "created": 3.1000000000000001,
      "id": "calendar-o",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "id": "fire-extinguisher",
      "unicode": "f134",
      "name": "fire-extinguisher",
      "categories": [
        "Web Application Icons"
      ],
      "created": 3.1000000000000001
    },
    {
      "filter": [
        "app"
      ],
      "name": "rocket",
      "unicode": "f135",
      "created": 3.1000000000000001,
      "id": "rocket",
      "categories": [
        "Web Application Icons",
        "Transportation Icons"
      ]
    },
    {
      "id": "maxcdn",
      "unicode": "f136",
      "name": "MaxCDN",
      "categories": [
        "Brand Icons"
      ],
      "created": 3.1000000000000001
    },
    {
      "filter": [
        "previous",
        "back",
        "arrow"
      ],
      "name": "Chevron Circle Left",
      "unicode": "f137",
      "created": 3.1000000000000001,
      "id": "chevron-circle-left",
      "categories": [
        "Directional Icons"
      ]
    },
    {
      "filter": [
        "next",
        "forward",
        "arrow"
      ],
      "name": "Chevron Circle Right",
      "unicode": "f138",
      "created": 3.1000000000000001,
      "id": "chevron-circle-right",
      "categories": [
        "Directional Icons"
      ]
    },
    {
      "filter": [
        "arrow"
      ],
      "name": "Chevron Circle Up",
      "unicode": "f139",
      "created": 3.1000000000000001,
      "id": "chevron-circle-up",
      "categories": [
        "Directional Icons"
      ]
    },
    {
      "filter": [
        "more",
        "dropdown",
        "menu",
        "arrow"
      ],
      "name": "Chevron Circle Down",
      "unicode": "f13a",
      "created": 3.1000000000000001,
      "id": "chevron-circle-down",
      "categories": [
        "Directional Icons"
      ]
    },
    {
      "code": [
        "code",
        "html5"
      ],
      "name": "HTML 5 Logo",
      "unicode": "f13b",
      "created": 3.1000000000000001,
      "id": "html5",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "filter": [
        "code"
      ],
      "name": "CSS 3 Logo",
      "unicode": "f13c",
      "created": 3.1000000000000001,
      "id": "css3",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "filter": [
        "link"
      ],
      "name": "Anchor",
      "unicode": "f13d",
      "created": 3.1000000000000001,
      "id": "anchor",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "protect",
        "admin",
        "password",
        "lock"
      ],
      "name": "Unlock Alt",
      "unicode": "f13e",
      "created": 3.1000000000000001,
      "id": "unlock-alt",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "target"
      ],
      "name": "Bullseye",
      "unicode": "f140",
      "created": 3.1000000000000001,
      "id": "bullseye",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "dots"
      ],
      "name": "Ellipsis Horizontal",
      "unicode": "f141",
      "created": 3.1000000000000001,
      "id": "ellipsis-h",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "dots"
      ],
      "name": "Ellipsis Vertical",
      "unicode": "f142",
      "created": 3.1000000000000001,
      "id": "ellipsis-v",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "feed",
        "blog"
      ],
      "name": "RSS Square",
      "unicode": "f143",
      "created": 3.1000000000000001,
      "id": "rss-square",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "start",
        "playing"
      ],
      "name": "Play Circle",
      "unicode": "f144",
      "created": 3.1000000000000001,
      "id": "play-circle",
      "categories": [
        "Video Player Icons"
      ]
    },
    {
      "filter": [
        "movie",
        "pass",
        "support"
      ],
      "name": "Ticket",
      "unicode": "f145",
      "created": 3.1000000000000001,
      "id": "ticket",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "hide",
        "minify",
        "delete",
        "remove",
        "trash",
        "hide",
        "collapse"
      ],
      "name": "Minus Square",
      "unicode": "f146",
      "created": 3.1000000000000001,
      "id": "minus-square",
      "categories": [
        "Web Application Icons",
        "Form Control Icons"
      ]
    },
    {
      "filter": [
        "hide",
        "minify",
        "delete",
        "remove",
        "trash",
        "hide",
        "collapse"
      ],
      "name": "Minus Square Outlined",
      "unicode": "f147",
      "created": 3.1000000000000001,
      "id": "minus-square-o",
      "categories": [
        "Web Application Icons",
        "Form Control Icons"
      ]
    },
    {
      "filter": [
        "arrow"
      ],
      "name": "Level Up",
      "unicode": "f148",
      "created": 3.1000000000000001,
      "id": "level-up",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "arrow"
      ],
      "name": "Level Down",
      "unicode": "f149",
      "created": 3.1000000000000001,
      "id": "level-down",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "checkmark",
        "done",
        "todo",
        "agree",
        "accept",
        "confirm",
        "ok"
      ],
      "name": "Check Square",
      "unicode": "f14a",
      "created": 3.1000000000000001,
      "id": "check-square",
      "categories": [
        "Web Application Icons",
        "Form Control Icons"
      ]
    },
    {
      "filter": [
        "write",
        "edit",
        "update"
      ],
      "name": "Pencil Square",
      "unicode": "f14b",
      "created": 3.1000000000000001,
      "id": "pencil-square",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "open",
        "new"
      ],
      "name": "External Link Square",
      "unicode": "f14c",
      "created": 3.1000000000000001,
      "id": "external-link-square",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "social",
        "send"
      ],
      "name": "Share Square",
      "unicode": "f14d",
      "created": 3.1000000000000001,
      "id": "share-square",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "safari",
        "directory",
        "menu",
        "location"
      ],
      "name": "Compass",
      "unicode": "f14e",
      "created": 3.2000000000000002,
      "id": "compass",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "more",
        "dropdown",
        "menu"
      ],
      "name": "Caret Square Outlined Down",
      "unicode": "f150",
      "created": 3.2000000000000002,
      "id": "caret-square-o-down",
      "categories": [
        "Web Application Icons",
        "Directional Icons"
      ],
      "aliases": [
        "toggle-down"
      ]
    },
    {
      "name": "Caret Square Outlined Up",
      "unicode": "f151",
      "created": 3.2000000000000002,
      "id": "caret-square-o-up",
      "categories": [
        "Web Application Icons",
        "Directional Icons"
      ],
      "aliases": [
        "toggle-up"
      ]
    },
    {
      "filter": [
        "next",
        "forward"
      ],
      "name": "Caret Square Outlined Right",
      "unicode": "f152",
      "created": 3.2000000000000002,
      "id": "caret-square-o-right",
      "categories": [
        "Web Application Icons",
        "Directional Icons"
      ],
      "aliases": [
        "toggle-right"
      ]
    },
    {
      "name": "Euro (EUR)",
      "unicode": "f153",
      "created": 3.2000000000000002,
      "id": "eur",
      "categories": [
        "Currency Icons"
      ],
      "aliases": [
        "euro"
      ]
    },
    {
      "id": "gbp",
      "unicode": "f154",
      "name": "GBP",
      "categories": [
        "Currency Icons"
      ],
      "created": 3.2000000000000002
    },
    {
      "name": "US Dollar",
      "unicode": "f155",
      "created": 3.2000000000000002,
      "id": "usd",
      "categories": [
        "Currency Icons"
      ],
      "aliases": [
        "dollar"
      ]
    },
    {
      "name": "Indian Rupee (INR)",
      "unicode": "f156",
      "created": 3.2000000000000002,
      "id": "inr",
      "categories": [
        "Currency Icons"
      ],
      "aliases": [
        "rupee"
      ]
    },
    {
      "name": "Japanese Yen (JPY)",
      "unicode": "f157",
      "created": 3.2000000000000002,
      "id": "jpy",
      "categories": [
        "Currency Icons"
      ],
      "aliases": [
        "cny",
        "rmb",
        "yen"
      ]
    },
    {
      "name": "Russian Ruble (RUB)",
      "unicode": "f158",
      "created": 4.0,
      "id": "rub",
      "categories": [
        "Currency Icons"
      ],
      "aliases": [
        "ruble",
        "rouble"
      ]
    },
    {
      "name": "Korean Won (KRW)",
      "unicode": "f159",
      "created": 3.2000000000000002,
      "id": "krw",
      "categories": [
        "Currency Icons"
      ],
      "aliases": [
        "won"
      ]
    },
    {
      "name": "Bitcoin (BTC)",
      "unicode": "f15a",
      "created": 3.2000000000000002,
      "id": "btc",
      "categories": [
        "Currency Icons",
        "Brand Icons"
      ],
      "aliases": [
        "bitcoin"
      ]
    },
    {
      "filter": [
        "new",
        "page",
        "pdf",
        "document"
      ],
      "name": "File",
      "unicode": "f15b",
      "created": 3.2000000000000002,
      "id": "file",
      "categories": [
        "Text Editor Icons",
        "File Type Icons"
      ]
    },
    {
      "filter": [
        "new",
        "page",
        "pdf",
        "document"
      ],
      "name": "File Text",
      "unicode": "f15c",
      "created": 3.2000000000000002,
      "id": "file-text",
      "categories": [
        "Text Editor Icons",
        "File Type Icons"
      ]
    },
    {
      "id": "sort-alpha-asc",
      "unicode": "f15d",
      "name": "Sort Alpha Ascending",
      "categories": [
        "Web Application Icons"
      ],
      "created": 3.2000000000000002
    },
    {
      "id": "sort-alpha-desc",
      "unicode": "f15e",
      "name": "Sort Alpha Descending",
      "categories": [
        "Web Application Icons"
      ],
      "created": 3.2000000000000002
    },
    {
      "id": "sort-amount-asc",
      "unicode": "f160",
      "name": "Sort Amount Ascending",
      "categories": [
        "Web Application Icons"
      ],
      "created": 3.2000000000000002
    },
    {
      "id": "sort-amount-desc",
      "unicode": "f161",
      "name": "Sort Amount Descending",
      "categories": [
        "Web Application Icons"
      ],
      "created": 3.2000000000000002
    },
    {
      "filter": [
        "numbers"
      ],
      "name": "Sort Numeric Ascending",
      "unicode": "f162",
      "created": 3.2000000000000002,
      "id": "sort-numeric-asc",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "numbers"
      ],
      "name": "Sort Numeric Descending",
      "unicode": "f163",
      "created": 3.2000000000000002,
      "id": "sort-numeric-desc",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "like",
        "favorite",
        "approve",
        "agree",
        "hand"
      ],
      "name": "thumbs-up",
      "unicode": "f164",
      "created": 3.2000000000000002,
      "id": "thumbs-up",
      "categories": [
        "Web Application Icons",
        "Hand Icons"
      ]
    },
    {
      "filter": [
        "dislike",
        "disapprove",
        "disagree",
        "hand"
      ],
      "name": "thumbs-down",
      "unicode": "f165",
      "created": 3.2000000000000002,
      "id": "thumbs-down",
      "categories": [
        "Web Application Icons",
        "Hand Icons"
      ]
    },
    {
      "filter": [
        "video",
        "film"
      ],
      "name": "YouTube Square",
      "unicode": "f166",
      "created": 3.2000000000000002,
      "id": "youtube-square",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "filter": [
        "video",
        "film"
      ],
      "name": "YouTube",
      "unicode": "f167",
      "created": 3.2000000000000002,
      "id": "youtube",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "id": "xing",
      "unicode": "f168",
      "name": "Xing",
      "categories": [
        "Brand Icons"
      ],
      "created": 3.2000000000000002
    },
    {
      "id": "xing-square",
      "unicode": "f169",
      "name": "Xing Square",
      "categories": [
        "Brand Icons"
      ],
      "created": 3.2000000000000002
    },
    {
      "filter": [
        "start",
        "playing"
      ],
      "name": "YouTube Play",
      "unicode": "f16a",
      "created": 3.2000000000000002,
      "id": "youtube-play",
      "categories": [
        "Brand Icons",
        "Video Player Icons"
      ]
    },
    {
      "id": "dropbox",
      "unicode": "f16b",
      "name": "Dropbox",
      "categories": [
        "Brand Icons"
      ],
      "created": 3.2000000000000002
    },
    {
      "id": "stack-overflow",
      "unicode": "f16c",
      "name": "Stack Overflow",
      "categories": [
        "Brand Icons"
      ],
      "created": 3.2000000000000002
    },
    {
      "id": "instagram",
      "unicode": "f16d",
      "name": "Instagram",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.5999999999999996
    },
    {
      "id": "flickr",
      "unicode": "f16e",
      "name": "Flickr",
      "categories": [
        "Brand Icons"
      ],
      "created": 3.2000000000000002
    },
    {
      "id": "adn",
      "unicode": "f170",
      "name": "App.net",
      "categories": [
        "Brand Icons"
      ],
      "created": 3.2000000000000002
    },
    {
      "filter": [
        "git"
      ],
      "name": "Bitbucket",
      "unicode": "f171",
      "created": 3.2000000000000002,
      "id": "bitbucket",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "filter": [
        "git"
      ],
      "name": "Bitbucket Square",
      "unicode": "f172",
      "created": 3.2000000000000002,
      "id": "bitbucket-square",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "id": "tumblr",
      "unicode": "f173",
      "name": "Tumblr",
      "categories": [
        "Brand Icons"
      ],
      "created": 3.2000000000000002
    },
    {
      "id": "tumblr-square",
      "unicode": "f174",
      "name": "Tumblr Square",
      "categories": [
        "Brand Icons"
      ],
      "created": 3.2000000000000002
    },
    {
      "id": "long-arrow-down",
      "unicode": "f175",
      "name": "Long Arrow Down",
      "categories": [
        "Directional Icons"
      ],
      "created": 3.2000000000000002
    },
    {
      "id": "long-arrow-up",
      "unicode": "f176",
      "name": "Long Arrow Up",
      "categories": [
        "Directional Icons"
      ],
      "created": 3.2000000000000002
    },
    {
      "filter": [
        "previous",
        "back"
      ],
      "name": "Long Arrow Left",
      "unicode": "f177",
      "created": 3.2000000000000002,
      "id": "long-arrow-left",
      "categories": [
        "Directional Icons"
      ]
    },
    {
      "id": "long-arrow-right",
      "unicode": "f178",
      "name": "Long Arrow Right",
      "categories": [
        "Directional Icons"
      ],
      "created": 3.2000000000000002
    },
    {
      "filter": [
        "osx",
        "food"
      ],
      "name": "Apple",
      "unicode": "f179",
      "created": 3.2000000000000002,
      "id": "apple",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "filter": [
        "microsoft"
      ],
      "name": "Windows",
      "unicode": "f17a",
      "created": 3.2000000000000002,
      "id": "windows",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "filter": [
        "robot"
      ],
      "name": "Android",
      "unicode": "f17b",
      "created": 3.2000000000000002,
      "id": "android",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "filter": [
        "tux"
      ],
      "name": "Linux",
      "unicode": "f17c",
      "created": 3.2000000000000002,
      "id": "linux",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "id": "dribbble",
      "unicode": "f17d",
      "name": "Dribbble",
      "categories": [
        "Brand Icons"
      ],
      "created": 3.2000000000000002
    },
    {
      "id": "skype",
      "unicode": "f17e",
      "name": "Skype",
      "categories": [
        "Brand Icons"
      ],
      "created": 3.2000000000000002
    },
    {
      "id": "foursquare",
      "unicode": "f180",
      "name": "Foursquare",
      "categories": [
        "Brand Icons"
      ],
      "created": 3.2000000000000002
    },
    {
      "id": "trello",
      "unicode": "f181",
      "name": "Trello",
      "categories": [
        "Brand Icons"
      ],
      "created": 3.2000000000000002
    },
    {
      "filter": [
        "woman",
        "user",
        "person",
        "profile"
      ],
      "name": "Female",
      "unicode": "f182",
      "created": 3.2000000000000002,
      "id": "female",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "man",
        "user",
        "person",
        "profile"
      ],
      "name": "Male",
      "unicode": "f183",
      "created": 3.2000000000000002,
      "id": "male",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "heart",
        "like",
        "favorite",
        "love"
      ],
      "name": "Gratipay (Gittip)",
      "unicode": "f184",
      "created": 3.2000000000000002,
      "id": "gratipay",
      "categories": [
        "Brand Icons"
      ],
      "aliases": [
        "gittip"
      ]
    },
    {
      "filter": [
        "weather",
        "contrast",
        "lighter",
        "brighten",
        "day"
      ],
      "name": "Sun Outlined",
      "unicode": "f185",
      "created": 3.2000000000000002,
      "id": "sun-o",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "night",
        "darker",
        "contrast"
      ],
      "name": "Moon Outlined",
      "unicode": "f186",
      "created": 3.2000000000000002,
      "id": "moon-o",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "box",
        "storage"
      ],
      "name": "Archive",
      "unicode": "f187",
      "created": 3.2000000000000002,
      "id": "archive",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "report",
        "insect"
      ],
      "name": "Bug",
      "unicode": "f188",
      "created": 3.2000000000000002,
      "id": "bug",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "id": "vk",
      "unicode": "f189",
      "name": "VK",
      "categories": [
        "Brand Icons"
      ],
      "created": 3.2000000000000002
    },
    {
      "id": "weibo",
      "unicode": "f18a",
      "name": "Weibo",
      "categories": [
        "Brand Icons"
      ],
      "created": 3.2000000000000002
    },
    {
      "id": "renren",
      "unicode": "f18b",
      "name": "Renren",
      "categories": [
        "Brand Icons"
      ],
      "created": 3.2000000000000002
    },
    {
      "filter": [
        "leaf",
        "leaves",
        "tree",
        "plant",
        "eco",
        "nature"
      ],
      "name": "Pagelines",
      "unicode": "f18c",
      "created": 4.0,
      "id": "pagelines",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "id": "stack-exchange",
      "unicode": "f18d",
      "name": "Stack Exchange",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.0
    },
    {
      "filter": [
        "next",
        "forward"
      ],
      "name": "Arrow Circle Outlined Right",
      "unicode": "f18e",
      "created": 4.0,
      "id": "arrow-circle-o-right",
      "categories": [
        "Directional Icons"
      ]
    },
    {
      "filter": [
        "previous",
        "back"
      ],
      "name": "Arrow Circle Outlined Left",
      "unicode": "f190",
      "created": 4.0,
      "id": "arrow-circle-o-left",
      "categories": [
        "Directional Icons"
      ]
    },
    {
      "filter": [
        "previous",
        "back"
      ],
      "name": "Caret Square Outlined Left",
      "unicode": "f191",
      "created": 4.0,
      "id": "caret-square-o-left",
      "categories": [
        "Web Application Icons",
        "Directional Icons"
      ],
      "aliases": [
        "toggle-left"
      ]
    },
    {
      "filter": [
        "target",
        "bullseye",
        "notification"
      ],
      "name": "Dot Circle Outlined",
      "unicode": "f192",
      "created": 4.0,
      "id": "dot-circle-o",
      "categories": [
        "Web Application Icons",
        "Form Control Icons"
      ]
    },
    {
      "filter": [
        "handicap",
        "person"
      ],
      "name": "Wheelchair",
      "unicode": "f193",
      "created": 4.0,
      "id": "wheelchair",
      "categories": [
        "Web Application Icons",
        "Medical Icons",
        "Transportation Icons",
        "Accessibility Icons"
      ]
    },
    {
      "id": "vimeo-square",
      "unicode": "f194",
      "name": "Vimeo Square",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.0
    },
    {
      "name": "Turkish Lira (TRY)",
      "unicode": "f195",
      "created": 4.0,
      "id": "try",
      "categories": [
        "Currency Icons"
      ],
      "aliases": [
        "turkish-lira"
      ]
    },
    {
      "filter": [
        "add",
        "new",
        "create",
        "expand"
      ],
      "name": "Plus Square Outlined",
      "unicode": "f196",
      "created": 4.0,
      "id": "plus-square-o",
      "categories": [
        "Web Application Icons",
        "Form Control Icons"
      ]
    },
    {
      "filter": null,
      "name": "Space Shuttle",
      "unicode": "f197",
      "created": 4.0999999999999996,
      "id": "space-shuttle",
      "categories": [
        "Web Application Icons",
        "Transportation Icons"
      ]
    },
    {
      "filter": [
        "hashtag",
        "anchor",
        "hash"
      ],
      "name": "Slack Logo",
      "unicode": "f198",
      "created": 4.0999999999999996,
      "id": "slack",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "id": "envelope-square",
      "unicode": "f199",
      "name": "Envelope Square",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "wordpress",
      "unicode": "f19a",
      "name": "WordPress Logo",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "openid",
      "unicode": "f19b",
      "name": "OpenID",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "name": "University",
      "unicode": "f19c",
      "created": 4.0999999999999996,
      "id": "university",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "institution",
        "bank"
      ]
    },
    {
      "filter": [
        "learning",
        "school",
        "student"
      ],
      "name": "Graduation Cap",
      "unicode": "f19d",
      "created": 4.0999999999999996,
      "id": "graduation-cap",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "mortar-board"
      ]
    },
    {
      "id": "yahoo",
      "unicode": "f19e",
      "name": "Yahoo Logo",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "google",
      "unicode": "f1a0",
      "name": "Google Logo",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "reddit",
      "unicode": "f1a1",
      "name": "reddit Logo",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "reddit-square",
      "unicode": "f1a2",
      "name": "reddit Square",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "stumbleupon-circle",
      "unicode": "f1a3",
      "name": "StumbleUpon Circle",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "stumbleupon",
      "unicode": "f1a4",
      "name": "StumbleUpon Logo",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "delicious",
      "unicode": "f1a5",
      "name": "Delicious Logo",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "digg",
      "unicode": "f1a6",
      "name": "Digg Logo",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "pied-piper-pp",
      "unicode": "f1a7",
      "name": "Pied Piper PP Logo (Old)",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "pied-piper-alt",
      "unicode": "f1a8",
      "name": "Pied Piper Alternate Logo",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "drupal",
      "unicode": "f1a9",
      "name": "Drupal Logo",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "joomla",
      "unicode": "f1aa",
      "name": "Joomla Logo",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "language",
      "unicode": "f1ab",
      "name": "Language",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "fax",
      "unicode": "f1ac",
      "name": "Fax",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "filter": [
        "work",
        "business",
        "apartment",
        "office",
        "company"
      ],
      "name": "Building",
      "unicode": "f1ad",
      "created": 4.0999999999999996,
      "id": "building",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "id": "child",
      "unicode": "f1ae",
      "name": "Child",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "filter": [
        "pet"
      ],
      "name": "Paw",
      "unicode": "f1b0",
      "created": 4.0999999999999996,
      "id": "paw",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "id": "spoon",
      "unicode": "f1b1",
      "name": "spoon",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "cube",
      "unicode": "f1b2",
      "name": "Cube",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "cubes",
      "unicode": "f1b3",
      "name": "Cubes",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "behance",
      "unicode": "f1b4",
      "name": "Behance",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "behance-square",
      "unicode": "f1b5",
      "name": "Behance Square",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "steam",
      "unicode": "f1b6",
      "name": "Steam",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "steam-square",
      "unicode": "f1b7",
      "name": "Steam Square",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "recycle",
      "unicode": "f1b8",
      "name": "Recycle",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "filter": [
        "vehicle"
      ],
      "name": "Car",
      "unicode": "f1b9",
      "created": 4.0999999999999996,
      "id": "car",
      "categories": [
        "Web Application Icons",
        "Transportation Icons"
      ],
      "aliases": [
        "automobile"
      ]
    },
    {
      "filter": [
        "vehicle"
      ],
      "name": "Taxi",
      "unicode": "f1ba",
      "created": 4.0999999999999996,
      "id": "taxi",
      "categories": [
        "Web Application Icons",
        "Transportation Icons"
      ],
      "aliases": [
        "cab"
      ]
    },
    {
      "id": "tree",
      "unicode": "f1bb",
      "name": "Tree",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "spotify",
      "unicode": "f1bc",
      "name": "Spotify",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "deviantart",
      "unicode": "f1bd",
      "name": "deviantART",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "soundcloud",
      "unicode": "f1be",
      "name": "SoundCloud",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "database",
      "unicode": "f1c0",
      "name": "Database",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "file-pdf-o",
      "unicode": "f1c1",
      "name": "PDF File Outlined",
      "categories": [
        "Web Application Icons",
        "File Type Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "file-word-o",
      "unicode": "f1c2",
      "name": "Word File Outlined",
      "categories": [
        "Web Application Icons",
        "File Type Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "file-excel-o",
      "unicode": "f1c3",
      "name": "Excel File Outlined",
      "categories": [
        "Web Application Icons",
        "File Type Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "file-powerpoint-o",
      "unicode": "f1c4",
      "name": "Powerpoint File Outlined",
      "categories": [
        "Web Application Icons",
        "File Type Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "name": "Image File Outlined",
      "unicode": "f1c5",
      "created": 4.0999999999999996,
      "id": "file-image-o",
      "categories": [
        "Web Application Icons",
        "File Type Icons"
      ],
      "aliases": [
        "file-photo-o",
        "file-picture-o"
      ]
    },
    {
      "name": "Archive File Outlined",
      "unicode": "f1c6",
      "created": 4.0999999999999996,
      "id": "file-archive-o",
      "categories": [
        "Web Application Icons",
        "File Type Icons"
      ],
      "aliases": [
        "file-zip-o"
      ]
    },
    {
      "name": "Audio File Outlined",
      "unicode": "f1c7",
      "created": 4.0999999999999996,
      "id": "file-audio-o",
      "categories": [
        "Web Application Icons",
        "File Type Icons"
      ],
      "aliases": [
        "file-sound-o"
      ]
    },
    {
      "name": "Video File Outlined",
      "unicode": "f1c8",
      "created": 4.0999999999999996,
      "id": "file-video-o",
      "categories": [
        "Web Application Icons",
        "File Type Icons"
      ],
      "aliases": [
        "file-movie-o"
      ]
    },
    {
      "id": "file-code-o",
      "unicode": "f1c9",
      "name": "Code File Outlined",
      "categories": [
        "Web Application Icons",
        "File Type Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "vine",
      "unicode": "f1ca",
      "name": "Vine",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "codepen",
      "unicode": "f1cb",
      "name": "Codepen",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "jsfiddle",
      "unicode": "f1cc",
      "name": "jsFiddle",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "name": "Life Ring",
      "unicode": "f1cd",
      "created": 4.0999999999999996,
      "id": "life-ring",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "life-bouy",
        "life-buoy",
        "life-saver",
        "support"
      ]
    },
    {
      "id": "circle-o-notch",
      "unicode": "f1ce",
      "name": "Circle Outlined Notched",
      "categories": [
        "Web Application Icons",
        "Spinner Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "name": "Rebel Alliance",
      "unicode": "f1d0",
      "created": 4.0999999999999996,
      "id": "rebel",
      "categories": [
        "Brand Icons"
      ],
      "aliases": [
        "ra",
        "resistance"
      ]
    },
    {
      "name": "Galactic Empire",
      "unicode": "f1d1",
      "created": 4.0999999999999996,
      "id": "empire",
      "categories": [
        "Brand Icons"
      ],
      "aliases": [
        "ge"
      ]
    },
    {
      "id": "git-square",
      "unicode": "f1d2",
      "name": "Git Square",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "git",
      "unicode": "f1d3",
      "name": "Git",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "name": "Hacker News",
      "unicode": "f1d4",
      "created": 4.0999999999999996,
      "id": "hacker-news",
      "categories": [
        "Brand Icons"
      ],
      "aliases": [
        "y-combinator-square",
        "yc-square"
      ]
    },
    {
      "id": "tencent-weibo",
      "unicode": "f1d5",
      "name": "Tencent Weibo",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "qq",
      "unicode": "f1d6",
      "name": "QQ",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "name": "Weixin (WeChat)",
      "unicode": "f1d7",
      "created": 4.0999999999999996,
      "id": "weixin",
      "categories": [
        "Brand Icons"
      ],
      "aliases": [
        "wechat"
      ]
    },
    {
      "name": "Paper Plane",
      "unicode": "f1d8",
      "created": 4.0999999999999996,
      "id": "paper-plane",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "send"
      ]
    },
    {
      "name": "Paper Plane Outlined",
      "unicode": "f1d9",
      "created": 4.0999999999999996,
      "id": "paper-plane-o",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "send-o"
      ]
    },
    {
      "id": "history",
      "unicode": "f1da",
      "name": "History",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "circle-thin",
      "unicode": "f1db",
      "name": "Circle Outlined Thin",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "filter": [
        "heading"
      ],
      "name": "header",
      "unicode": "f1dc",
      "created": 4.0999999999999996,
      "id": "header",
      "categories": [
        "Text Editor Icons"
      ]
    },
    {
      "id": "paragraph",
      "unicode": "f1dd",
      "name": "paragraph",
      "categories": [
        "Text Editor Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "filter": [
        "settings"
      ],
      "name": "Sliders",
      "unicode": "f1de",
      "created": 4.0999999999999996,
      "id": "sliders",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "id": "share-alt",
      "unicode": "f1e0",
      "name": "Share Alt",
      "categories": [
        "Web Application Icons",
        "Brand Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "share-alt-square",
      "unicode": "f1e1",
      "name": "Share Alt Square",
      "categories": [
        "Web Application Icons",
        "Brand Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "id": "bomb",
      "unicode": "f1e2",
      "name": "Bomb",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.0999999999999996
    },
    {
      "name": "Futbol Outlined",
      "unicode": "f1e3",
      "created": 4.2000000000000002,
      "id": "futbol-o",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "soccer-ball-o"
      ]
    },
    {
      "id": "tty",
      "unicode": "f1e4",
      "name": "TTY",
      "categories": [
        "Web Application Icons",
        "Accessibility Icons"
      ],
      "created": 4.2000000000000002
    },
    {
      "id": "binoculars",
      "unicode": "f1e5",
      "name": "Binoculars",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.2000000000000002
    },
    {
      "filter": [
        "power",
        "connect"
      ],
      "name": "Plug",
      "unicode": "f1e6",
      "created": 4.2000000000000002,
      "id": "plug",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "id": "slideshare",
      "unicode": "f1e7",
      "name": "Slideshare",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.2000000000000002
    },
    {
      "id": "twitch",
      "unicode": "f1e8",
      "name": "Twitch",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.2000000000000002
    },
    {
      "id": "yelp",
      "unicode": "f1e9",
      "name": "Yelp",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.2000000000000002
    },
    {
      "filter": [
        "press"
      ],
      "name": "Newspaper Outlined",
      "unicode": "f1ea",
      "created": 4.2000000000000002,
      "id": "newspaper-o",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "id": "wifi",
      "unicode": "f1eb",
      "name": "WiFi",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.2000000000000002
    },
    {
      "id": "calculator",
      "unicode": "f1ec",
      "name": "Calculator",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.2000000000000002
    },
    {
      "id": "paypal",
      "unicode": "f1ed",
      "name": "Paypal",
      "categories": [
        "Brand Icons",
        "Payment Icons"
      ],
      "created": 4.2000000000000002
    },
    {
      "id": "google-wallet",
      "unicode": "f1ee",
      "name": "Google Wallet",
      "categories": [
        "Brand Icons",
        "Payment Icons"
      ],
      "created": 4.2000000000000002
    },
    {
      "id": "cc-visa",
      "unicode": "f1f0",
      "name": "Visa Credit Card",
      "categories": [
        "Brand Icons",
        "Payment Icons"
      ],
      "created": 4.2000000000000002
    },
    {
      "id": "cc-mastercard",
      "unicode": "f1f1",
      "name": "MasterCard Credit Card",
      "categories": [
        "Brand Icons",
        "Payment Icons"
      ],
      "created": 4.2000000000000002
    },
    {
      "id": "cc-discover",
      "unicode": "f1f2",
      "name": "Discover Credit Card",
      "categories": [
        "Brand Icons",
        "Payment Icons"
      ],
      "created": 4.2000000000000002
    },
    {
      "filter": [
        "amex"
      ],
      "name": "American Express Credit Card",
      "unicode": "f1f3",
      "created": 4.2000000000000002,
      "id": "cc-amex",
      "categories": [
        "Brand Icons",
        "Payment Icons"
      ]
    },
    {
      "id": "cc-paypal",
      "unicode": "f1f4",
      "name": "Paypal Credit Card",
      "categories": [
        "Brand Icons",
        "Payment Icons"
      ],
      "created": 4.2000000000000002
    },
    {
      "id": "cc-stripe",
      "unicode": "f1f5",
      "name": "Stripe Credit Card",
      "categories": [
        "Brand Icons",
        "Payment Icons"
      ],
      "created": 4.2000000000000002
    },
    {
      "id": "bell-slash",
      "unicode": "f1f6",
      "name": "Bell Slash",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.2000000000000002
    },
    {
      "id": "bell-slash-o",
      "unicode": "f1f7",
      "name": "Bell Slash Outlined",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.2000000000000002
    },
    {
      "filter": [
        "garbage",
        "delete",
        "remove",
        "hide"
      ],
      "name": "Trash",
      "unicode": "f1f8",
      "created": 4.2000000000000002,
      "id": "trash",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "id": "copyright",
      "unicode": "f1f9",
      "name": "Copyright",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.2000000000000002
    },
    {
      "id": "at",
      "unicode": "f1fa",
      "name": "At",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.2000000000000002
    },
    {
      "id": "eyedropper",
      "unicode": "f1fb",
      "name": "Eyedropper",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.2000000000000002
    },
    {
      "id": "paint-brush",
      "unicode": "f1fc",
      "name": "Paint Brush",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.2000000000000002
    },
    {
      "id": "birthday-cake",
      "unicode": "f1fd",
      "name": "Birthday Cake",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.2000000000000002
    },
    {
      "filter": [
        "graph",
        "analytics"
      ],
      "name": "Area Chart",
      "unicode": "f1fe",
      "created": 4.2000000000000002,
      "id": "area-chart",
      "categories": [
        "Web Application Icons",
        "Chart Icons"
      ]
    },
    {
      "filter": [
        "graph",
        "analytics"
      ],
      "name": "Pie Chart",
      "unicode": "f200",
      "created": 4.2000000000000002,
      "id": "pie-chart",
      "categories": [
        "Web Application Icons",
        "Chart Icons"
      ]
    },
    {
      "filter": [
        "graph",
        "analytics"
      ],
      "name": "Line Chart",
      "unicode": "f201",
      "created": 4.2000000000000002,
      "id": "line-chart",
      "categories": [
        "Web Application Icons",
        "Chart Icons"
      ]
    },
    {
      "id": "lastfm",
      "unicode": "f202",
      "name": "last.fm",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.2000000000000002
    },
    {
      "id": "lastfm-square",
      "unicode": "f203",
      "name": "last.fm Square",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.2000000000000002
    },
    {
      "id": "toggle-off",
      "unicode": "f204",
      "name": "Toggle Off",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.2000000000000002
    },
    {
      "id": "toggle-on",
      "unicode": "f205",
      "name": "Toggle On",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.2000000000000002
    },
    {
      "filter": [
        "vehicle",
        "bike"
      ],
      "name": "Bicycle",
      "unicode": "f206",
      "created": 4.2000000000000002,
      "id": "bicycle",
      "categories": [
        "Web Application Icons",
        "Transportation Icons"
      ]
    },
    {
      "filter": [
        "vehicle"
      ],
      "name": "Bus",
      "unicode": "f207",
      "created": 4.2000000000000002,
      "id": "bus",
      "categories": [
        "Web Application Icons",
        "Transportation Icons"
      ]
    },
    {
      "name": "ioxhost",
      "unicode": "f208",
      "created": 4.2000000000000002,
      "url": "ioxhost.co.uk",
      "id": "ioxhost",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "id": "angellist",
      "unicode": "f209",
      "name": "AngelList",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.2000000000000002
    },
    {
      "id": "cc",
      "unicode": "f20a",
      "name": "Closed Captions",
      "categories": [
        "Web Application Icons",
        "Accessibility Icons"
      ],
      "created": 4.2000000000000002
    },
    {
      "name": "Shekel (ILS)",
      "unicode": "f20b",
      "created": 4.2000000000000002,
      "id": "ils",
      "categories": [
        "Currency Icons"
      ],
      "aliases": [
        "shekel",
        "sheqel"
      ]
    },
    {
      "name": "meanpath",
      "unicode": "f20c",
      "created": 4.2000000000000002,
      "url": "meanpath.com",
      "id": "meanpath",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "name": "BuySellAds",
      "unicode": "f20d",
      "created": 4.2999999999999998,
      "url": "buysellads.com",
      "id": "buysellads",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "name": "Connect Develop",
      "unicode": "f20e",
      "created": 4.2999999999999998,
      "url": "connectdevelop.com",
      "id": "connectdevelop",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "name": "DashCube",
      "unicode": "f210",
      "created": 4.2999999999999998,
      "url": "dashcube.com",
      "id": "dashcube",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "name": "Forumbee",
      "unicode": "f211",
      "created": 4.2999999999999998,
      "url": "forumbee.com",
      "id": "forumbee",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "name": "Leanpub",
      "unicode": "f212",
      "created": 4.2999999999999998,
      "url": "leanpub.com",
      "id": "leanpub",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "name": "Sellsy",
      "unicode": "f213",
      "created": 4.2999999999999998,
      "url": "sellsy.com",
      "id": "sellsy",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "name": "Shirts in Bulk",
      "unicode": "f214",
      "created": 4.2999999999999998,
      "url": "shirtsinbulk.com",
      "id": "shirtsinbulk",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "name": "SimplyBuilt",
      "unicode": "f215",
      "created": 4.2999999999999998,
      "url": "simplybuilt.com",
      "id": "simplybuilt",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "name": "skyatlas",
      "unicode": "f216",
      "created": 4.2999999999999998,
      "url": "skyatlas.com",
      "id": "skyatlas",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "filter": [
        "add",
        "shopping"
      ],
      "name": "Add to Shopping Cart",
      "unicode": "f217",
      "created": 4.2999999999999998,
      "id": "cart-plus",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "shopping"
      ],
      "name": "Shopping Cart Arrow Down",
      "unicode": "f218",
      "created": 4.2999999999999998,
      "id": "cart-arrow-down",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "gem",
        "gemstone"
      ],
      "name": "Diamond",
      "unicode": "f219",
      "created": 4.2999999999999998,
      "id": "diamond",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "boat",
        "sea"
      ],
      "name": "Ship",
      "unicode": "f21a",
      "created": 4.2999999999999998,
      "id": "ship",
      "categories": [
        "Web Application Icons",
        "Transportation Icons"
      ]
    },
    {
      "filter": [
        "whisper",
        "spy",
        "incognito",
        "privacy"
      ],
      "name": "User Secret",
      "unicode": "f21b",
      "created": 4.2999999999999998,
      "id": "user-secret",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "vehicle",
        "bike"
      ],
      "name": "Motorcycle",
      "unicode": "f21c",
      "created": 4.2999999999999998,
      "id": "motorcycle",
      "categories": [
        "Web Application Icons",
        "Transportation Icons"
      ]
    },
    {
      "filter": [
        "map"
      ],
      "name": "Street View",
      "unicode": "f21d",
      "created": 4.2999999999999998,
      "id": "street-view",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "ekg"
      ],
      "name": "Heartbeat",
      "unicode": "f21e",
      "created": 4.2999999999999998,
      "id": "heartbeat",
      "categories": [
        "Web Application Icons",
        "Medical Icons"
      ]
    },
    {
      "filter": [
        "female"
      ],
      "name": "Venus",
      "unicode": "f221",
      "created": 4.2999999999999998,
      "id": "venus",
      "categories": [
        "Gender Icons"
      ]
    },
    {
      "filter": [
        "male"
      ],
      "name": "Mars",
      "unicode": "f222",
      "created": 4.2999999999999998,
      "id": "mars",
      "categories": [
        "Gender Icons"
      ]
    },
    {
      "filter": [
        "transgender"
      ],
      "name": "Mercury",
      "unicode": "f223",
      "created": 4.2999999999999998,
      "id": "mercury",
      "categories": [
        "Gender Icons"
      ]
    },
    {
      "name": "Transgender",
      "unicode": "f224",
      "created": 4.2999999999999998,
      "id": "transgender",
      "categories": [
        "Gender Icons"
      ],
      "aliases": [
        "intersex"
      ]
    },
    {
      "id": "transgender-alt",
      "unicode": "f225",
      "name": "Transgender Alt",
      "categories": [
        "Gender Icons"
      ],
      "created": 4.2999999999999998
    },
    {
      "id": "venus-double",
      "unicode": "f226",
      "name": "Venus Double",
      "categories": [
        "Gender Icons"
      ],
      "created": 4.2999999999999998
    },
    {
      "id": "mars-double",
      "unicode": "f227",
      "name": "Mars Double",
      "categories": [
        "Gender Icons"
      ],
      "created": 4.2999999999999998
    },
    {
      "id": "venus-mars",
      "unicode": "f228",
      "name": "Venus Mars",
      "categories": [
        "Gender Icons"
      ],
      "created": 4.2999999999999998
    },
    {
      "id": "mars-stroke",
      "unicode": "f229",
      "name": "Mars Stroke",
      "categories": [
        "Gender Icons"
      ],
      "created": 4.2999999999999998
    },
    {
      "id": "mars-stroke-v",
      "unicode": "f22a",
      "name": "Mars Stroke Vertical",
      "categories": [
        "Gender Icons"
      ],
      "created": 4.2999999999999998
    },
    {
      "id": "mars-stroke-h",
      "unicode": "f22b",
      "name": "Mars Stroke Horizontal",
      "categories": [
        "Gender Icons"
      ],
      "created": 4.2999999999999998
    },
    {
      "id": "neuter",
      "unicode": "f22c",
      "name": "Neuter",
      "categories": [
        "Gender Icons"
      ],
      "created": 4.2999999999999998
    },
    {
      "id": "genderless",
      "unicode": "f22d",
      "name": "Genderless",
      "categories": [
        "Gender Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "id": "facebook-official",
      "unicode": "f230",
      "name": "Facebook Official",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.2999999999999998
    },
    {
      "id": "pinterest-p",
      "unicode": "f231",
      "name": "Pinterest P",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.2999999999999998
    },
    {
      "id": "whatsapp",
      "unicode": "f232",
      "name": "What\'s App",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.2999999999999998
    },
    {
      "id": "server",
      "unicode": "f233",
      "name": "Server",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.2999999999999998
    },
    {
      "filter": [
        "sign up",
        "signup"
      ],
      "name": "Add User",
      "unicode": "f234",
      "created": 4.2999999999999998,
      "id": "user-plus",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "id": "user-times",
      "unicode": "f235",
      "name": "Remove User",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.2999999999999998
    },
    {
      "filter": [
        "travel"
      ],
      "name": "Bed",
      "unicode": "f236",
      "created": 4.2999999999999998,
      "id": "bed",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "hotel"
      ]
    },
    {
      "name": "Viacoin",
      "unicode": "f237",
      "created": 4.2999999999999998,
      "url": "viacoin.org",
      "id": "viacoin",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "id": "train",
      "unicode": "f238",
      "name": "Train",
      "categories": [
        "Transportation Icons"
      ],
      "created": 4.2999999999999998
    },
    {
      "id": "subway",
      "unicode": "f239",
      "name": "Subway",
      "categories": [
        "Transportation Icons"
      ],
      "created": 4.2999999999999998
    },
    {
      "id": "medium",
      "unicode": "f23a",
      "name": "Medium",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.2999999999999998
    },
    {
      "name": "Y Combinator",
      "unicode": "f23b",
      "created": 4.4000000000000004,
      "id": "y-combinator",
      "categories": [
        "Brand Icons"
      ],
      "aliases": [
        "yc"
      ]
    },
    {
      "name": "Optin Monster",
      "unicode": "f23c",
      "created": 4.4000000000000004,
      "url": "optinmonster.com",
      "id": "optin-monster",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "name": "OpenCart",
      "unicode": "f23d",
      "created": 4.4000000000000004,
      "url": "opencart.com",
      "id": "opencart",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "id": "expeditedssl",
      "unicode": "f23e",
      "name": "ExpeditedSSL",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "filter": [
        "power"
      ],
      "name": "Battery Full",
      "unicode": "f240",
      "created": 4.4000000000000004,
      "id": "battery-full",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "battery-4",
        "battery"
      ]
    },
    {
      "filter": [
        "power"
      ],
      "name": "Battery 3/4 Full",
      "unicode": "f241",
      "created": 4.4000000000000004,
      "id": "battery-three-quarters",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "battery-3"
      ]
    },
    {
      "filter": [
        "power"
      ],
      "name": "Battery 1/2 Full",
      "unicode": "f242",
      "created": 4.4000000000000004,
      "id": "battery-half",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "battery-2"
      ]
    },
    {
      "filter": [
        "power"
      ],
      "name": "Battery 1/4 Full",
      "unicode": "f243",
      "created": 4.4000000000000004,
      "id": "battery-quarter",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "battery-1"
      ]
    },
    {
      "filter": [
        "power"
      ],
      "name": "Battery Empty",
      "unicode": "f244",
      "created": 4.4000000000000004,
      "id": "battery-empty",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "battery-0"
      ]
    },
    {
      "id": "mouse-pointer",
      "unicode": "f245",
      "name": "Mouse Pointer",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "id": "i-cursor",
      "unicode": "f246",
      "name": "I Beam Cursor",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "id": "object-group",
      "unicode": "f247",
      "name": "Object Group",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "id": "object-ungroup",
      "unicode": "f248",
      "name": "Object Ungroup",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "id": "sticky-note",
      "unicode": "f249",
      "name": "Sticky Note",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "id": "sticky-note-o",
      "unicode": "f24a",
      "name": "Sticky Note Outlined",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "id": "cc-jcb",
      "unicode": "f24b",
      "name": "JCB Credit Card",
      "categories": [
        "Brand Icons",
        "Payment Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "id": "cc-diners-club",
      "unicode": "f24c",
      "name": "Diner\'s Club Credit Card",
      "categories": [
        "Brand Icons",
        "Payment Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "filter": [
        "copy"
      ],
      "name": "Clone",
      "unicode": "f24d",
      "created": 4.4000000000000004,
      "id": "clone",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "id": "balance-scale",
      "unicode": "f24e",
      "name": "Balance Scale",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "id": "hourglass-o",
      "unicode": "f250",
      "name": "Hourglass Outlined",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "name": "Hourglass Start",
      "unicode": "f251",
      "created": 4.4000000000000004,
      "id": "hourglass-start",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "hourglass-1"
      ]
    },
    {
      "name": "Hourglass Half",
      "unicode": "f252",
      "created": 4.4000000000000004,
      "id": "hourglass-half",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "hourglass-2"
      ]
    },
    {
      "name": "Hourglass End",
      "unicode": "f253",
      "created": 4.4000000000000004,
      "id": "hourglass-end",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "hourglass-3"
      ]
    },
    {
      "id": "hourglass",
      "unicode": "f254",
      "name": "Hourglass",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "name": "Rock (Hand)",
      "unicode": "f255",
      "created": 4.4000000000000004,
      "id": "hand-rock-o",
      "categories": [
        "Web Application Icons",
        "Hand Icons"
      ],
      "aliases": [
        "hand-grab-o"
      ]
    },
    {
      "filter": [
        "stop"
      ],
      "name": "Paper (Hand)",
      "unicode": "f256",
      "created": 4.4000000000000004,
      "id": "hand-paper-o",
      "categories": [
        "Web Application Icons",
        "Hand Icons"
      ],
      "aliases": [
        "hand-stop-o"
      ]
    },
    {
      "id": "hand-scissors-o",
      "unicode": "f257",
      "name": "Scissors (Hand)",
      "categories": [
        "Web Application Icons",
        "Hand Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "id": "hand-lizard-o",
      "unicode": "f258",
      "name": "Lizard (Hand)",
      "categories": [
        "Web Application Icons",
        "Hand Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "id": "hand-spock-o",
      "unicode": "f259",
      "name": "Spock (Hand)",
      "categories": [
        "Web Application Icons",
        "Hand Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "id": "hand-pointer-o",
      "unicode": "f25a",
      "name": "Hand Pointer",
      "categories": [
        "Web Application Icons",
        "Hand Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "id": "hand-peace-o",
      "unicode": "f25b",
      "name": "Hand Peace",
      "categories": [
        "Web Application Icons",
        "Hand Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "id": "trademark",
      "unicode": "f25c",
      "name": "Trademark",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "id": "registered",
      "unicode": "f25d",
      "name": "Registered Trademark",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "id": "creative-commons",
      "unicode": "f25e",
      "name": "Creative Commons",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "id": "gg",
      "unicode": "f260",
      "name": "GG Currency",
      "categories": [
        "Currency Icons",
        "Brand Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "id": "gg-circle",
      "unicode": "f261",
      "name": "GG Currency Circle",
      "categories": [
        "Currency Icons",
        "Brand Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "id": "tripadvisor",
      "unicode": "f262",
      "name": "TripAdvisor",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "id": "odnoklassniki",
      "unicode": "f263",
      "name": "Odnoklassniki",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "id": "odnoklassniki-square",
      "unicode": "f264",
      "name": "Odnoklassniki Square",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "id": "get-pocket",
      "unicode": "f265",
      "name": "Get Pocket",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "id": "wikipedia-w",
      "unicode": "f266",
      "name": "Wikipedia W",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "filter": [
        "browser"
      ],
      "name": "Safari",
      "unicode": "f267",
      "created": 4.4000000000000004,
      "id": "safari",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "filter": [
        "browser"
      ],
      "name": "Chrome",
      "unicode": "f268",
      "created": 4.4000000000000004,
      "id": "chrome",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "filter": [
        "browser"
      ],
      "name": "Firefox",
      "unicode": "f269",
      "created": 4.4000000000000004,
      "id": "firefox",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "id": "opera",
      "unicode": "f26a",
      "name": "Opera",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "filter": [
        "browser",
        "ie"
      ],
      "name": "Internet-explorer",
      "unicode": "f26b",
      "created": 4.4000000000000004,
      "id": "internet-explorer",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "filter": [
        "display",
        "computer",
        "monitor"
      ],
      "name": "Television",
      "unicode": "f26c",
      "created": 4.4000000000000004,
      "id": "television",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "tv"
      ]
    },
    {
      "id": "contao",
      "unicode": "f26d",
      "name": "Contao",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "id": "500px",
      "unicode": "f26e",
      "name": "500px",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "id": "amazon",
      "unicode": "f270",
      "name": "Amazon",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "id": "calendar-plus-o",
      "unicode": "f271",
      "name": "Calendar Plus Outlined",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "id": "calendar-minus-o",
      "unicode": "f272",
      "name": "Calendar Minus Outlined",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "id": "calendar-times-o",
      "unicode": "f273",
      "name": "Calendar Times Outlined",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "filter": [
        "ok"
      ],
      "name": "Calendar Check Outlined",
      "unicode": "f274",
      "created": 4.4000000000000004,
      "id": "calendar-check-o",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "factory"
      ],
      "name": "Industry",
      "unicode": "f275",
      "created": 4.4000000000000004,
      "id": "industry",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "id": "map-pin",
      "unicode": "f276",
      "name": "Map Pin",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "id": "map-signs",
      "unicode": "f277",
      "name": "Map Signs",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "id": "map-o",
      "unicode": "f278",
      "name": "Map Outlined",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "id": "map",
      "unicode": "f279",
      "name": "Map",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "filter": [
        "speech",
        "notification",
        "note",
        "chat",
        "bubble",
        "feedback",
        "message",
        "texting",
        "sms",
        "conversation"
      ],
      "name": "Commenting",
      "unicode": "f27a",
      "created": 4.4000000000000004,
      "id": "commenting",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "filter": [
        "speech",
        "notification",
        "note",
        "chat",
        "bubble",
        "feedback",
        "message",
        "texting",
        "sms",
        "conversation"
      ],
      "name": "Commenting Outlined",
      "unicode": "f27b",
      "created": 4.4000000000000004,
      "id": "commenting-o",
      "categories": [
        "Web Application Icons"
      ]
    },
    {
      "id": "houzz",
      "unicode": "f27c",
      "name": "Houzz",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "id": "vimeo",
      "unicode": "f27d",
      "name": "Vimeo",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.4000000000000004
    },
    {
      "name": "Font Awesome Black Tie",
      "unicode": "f27e",
      "created": 4.4000000000000004,
      "url": "blacktie.io",
      "id": "black-tie",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "name": "Fonticons",
      "unicode": "f280",
      "created": 4.4000000000000004,
      "url": "fonticons.com",
      "id": "fonticons",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "id": "reddit-alien",
      "unicode": "f281",
      "name": "reddit Alien",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.5
    },
    {
      "filter": [
        "browser",
        "ie"
      ],
      "name": "Edge Browser",
      "unicode": "f282",
      "created": 4.5,
      "id": "edge",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "filter": [
        "money",
        "buy",
        "debit",
        "checkout",
        "purchase",
        "payment",
        "credit card"
      ],
      "name": "Credit Card",
      "unicode": "f283",
      "created": 4.5,
      "id": "credit-card-alt",
      "categories": [
        "Payment Icons",
        "Web Application Icons"
      ]
    },
    {
      "name": "Codie Pie",
      "unicode": "f284",
      "created": 4.5,
      "url": "codiepie.com",
      "id": "codiepie",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "id": "modx",
      "unicode": "f285",
      "name": "MODX",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.5
    },
    {
      "name": "Fort Awesome",
      "unicode": "f286",
      "created": 4.5,
      "url": "fortawesome.com",
      "id": "fort-awesome",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "id": "usb",
      "unicode": "f287",
      "name": "USB",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.5
    },
    {
      "id": "product-hunt",
      "unicode": "f288",
      "name": "Product Hunt",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.5
    },
    {
      "id": "mixcloud",
      "unicode": "f289",
      "name": "Mixcloud",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.5
    },
    {
      "id": "scribd",
      "unicode": "f28a",
      "name": "Scribd",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.5
    },
    {
      "id": "pause-circle",
      "unicode": "f28b",
      "name": "Pause Circle",
      "categories": [
        "Video Player Icons"
      ],
      "created": 4.5
    },
    {
      "id": "pause-circle-o",
      "unicode": "f28c",
      "name": "Pause Circle Outlined",
      "categories": [
        "Video Player Icons"
      ],
      "created": 4.5
    },
    {
      "id": "stop-circle",
      "unicode": "f28d",
      "name": "Stop Circle",
      "categories": [
        "Video Player Icons"
      ],
      "created": 4.5
    },
    {
      "id": "stop-circle-o",
      "unicode": "f28e",
      "name": "Stop Circle Outlined",
      "categories": [
        "Video Player Icons"
      ],
      "created": 4.5
    },
    {
      "id": "shopping-bag",
      "unicode": "f290",
      "name": "Shopping Bag",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.5
    },
    {
      "id": "shopping-basket",
      "unicode": "f291",
      "name": "Shopping Basket",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.5
    },
    {
      "id": "hashtag",
      "unicode": "f292",
      "name": "Hashtag",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.5
    },
    {
      "id": "bluetooth",
      "unicode": "f293",
      "name": "Bluetooth",
      "categories": [
        "Web Application Icons",
        "Brand Icons"
      ],
      "created": 4.5
    },
    {
      "id": "bluetooth-b",
      "unicode": "f294",
      "name": "Bluetooth",
      "categories": [
        "Web Application Icons",
        "Brand Icons"
      ],
      "created": 4.5
    },
    {
      "id": "percent",
      "unicode": "f295",
      "name": "Percent",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.5
    },
    {
      "name": "GitLab",
      "unicode": "f296",
      "created": 4.5999999999999996,
      "url": "gitlab.com",
      "id": "gitlab",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "name": "WPBeginner",
      "unicode": "f297",
      "created": 4.5999999999999996,
      "url": "wpbeginner.com",
      "id": "wpbeginner",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "name": "WPForms",
      "unicode": "f298",
      "created": 4.5999999999999996,
      "url": "wpforms.com",
      "id": "wpforms",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "filter": [
        "leaf"
      ],
      "name": "Envira Gallery",
      "unicode": "f299",
      "created": 4.5999999999999996,
      "url": "enviragallery.com",
      "id": "envira",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "id": "universal-access",
      "unicode": "f29a",
      "name": "Universal Access",
      "categories": [
        "Web Application Icons",
        "Accessibility Icons"
      ],
      "created": 4.5999999999999996
    },
    {
      "filter": [
        "handicap",
        "person"
      ],
      "name": "Wheelchair Alt",
      "unicode": "f29b",
      "created": 4.5999999999999996,
      "id": "wheelchair-alt",
      "categories": [
        "Web Application Icons",
        "Medical Icons",
        "Transportation Icons",
        "Accessibility Icons"
      ]
    },
    {
      "id": "question-circle-o",
      "unicode": "f29c",
      "name": "Question Circle Outlined",
      "categories": [
        "Web Application Icons",
        "Accessibility Icons"
      ],
      "created": 4.5999999999999996
    },
    {
      "id": "blind",
      "unicode": "f29d",
      "name": "Blind",
      "categories": [
        "Web Application Icons",
        "Accessibility Icons"
      ],
      "created": 4.5999999999999996
    },
    {
      "id": "audio-description",
      "unicode": "f29e",
      "name": "Audio Description",
      "categories": [
        "Web Application Icons",
        "Accessibility Icons"
      ],
      "created": 4.5999999999999996
    },
    {
      "filter": [
        "telephone"
      ],
      "name": "Volume Control Phone",
      "unicode": "f2a0",
      "created": 4.5999999999999996,
      "id": "volume-control-phone",
      "categories": [
        "Web Application Icons",
        "Accessibility Icons"
      ]
    },
    {
      "id": "braille",
      "unicode": "f2a1",
      "name": "Braille",
      "categories": [
        "Web Application Icons",
        "Accessibility Icons"
      ],
      "created": 4.5999999999999996
    },
    {
      "id": "assistive-listening-systems",
      "unicode": "f2a2",
      "name": "Assistive Listening Systems",
      "categories": [
        "Web Application Icons",
        "Accessibility Icons"
      ],
      "created": 4.5999999999999996
    },
    {
      "name": "American Sign Language Interpreting",
      "unicode": "f2a3",
      "created": 4.5999999999999996,
      "id": "american-sign-language-interpreting",
      "categories": [
        "Web Application Icons",
        "Accessibility Icons"
      ],
      "aliases": [
        "asl-interpreting"
      ]
    },
    {
      "name": "Deaf",
      "unicode": "f2a4",
      "created": 4.5999999999999996,
      "id": "deaf",
      "categories": [
        "Web Application Icons",
        "Accessibility Icons"
      ],
      "aliases": [
        "deafness",
        "hard-of-hearing"
      ]
    },
    {
      "id": "glide",
      "unicode": "f2a5",
      "name": "Glide",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.5999999999999996
    },
    {
      "id": "glide-g",
      "unicode": "f2a6",
      "name": "Glide G",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.5999999999999996
    },
    {
      "name": "Sign Language",
      "unicode": "f2a7",
      "created": 4.5999999999999996,
      "id": "sign-language",
      "categories": [
        "Web Application Icons",
        "Accessibility Icons"
      ],
      "aliases": [
        "signing"
      ]
    },
    {
      "id": "low-vision",
      "unicode": "f2a8",
      "name": "Low Vision",
      "categories": [
        "Web Application Icons",
        "Accessibility Icons"
      ],
      "created": 4.5999999999999996
    },
    {
      "id": "viadeo",
      "unicode": "f2a9",
      "name": "Viadeo",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.5999999999999996
    },
    {
      "id": "viadeo-square",
      "unicode": "f2aa",
      "name": "Viadeo Square",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.5999999999999996
    },
    {
      "id": "snapchat",
      "unicode": "f2ab",
      "name": "Snapchat",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.5999999999999996
    },
    {
      "id": "snapchat-ghost",
      "unicode": "f2ac",
      "name": "Snapchat Ghost",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.5999999999999996
    },
    {
      "id": "snapchat-square",
      "unicode": "f2ad",
      "name": "Snapchat Square",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.5999999999999996
    },
    {
      "id": "pied-piper",
      "unicode": "f2ae",
      "name": "Pied Piper Logo",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.5999999999999996
    },
    {
      "id": "first-order",
      "unicode": "f2b0",
      "name": "First Order",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.5999999999999996
    },
    {
      "name": "Yoast",
      "unicode": "f2b1",
      "created": 4.5999999999999996,
      "url": "yoast.com",
      "id": "yoast",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "name": "ThemeIsle",
      "unicode": "f2b2",
      "created": 4.5999999999999996,
      "url": "themeisle.com",
      "id": "themeisle",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "name": "Google Plus Official",
      "unicode": "f2b3",
      "created": 4.5999999999999996,
      "id": "google-plus-official",
      "categories": [
        "Brand Icons"
      ],
      "aliases": [
        "google-plus-circle"
      ]
    },
    {
      "name": "Font Awesome",
      "unicode": "f2b4",
      "created": 4.5999999999999996,
      "id": "font-awesome",
      "categories": [
        "Brand Icons"
      ],
      "aliases": [
        "fa"
      ]
    },
    {
      "id": "handshake-o",
      "unicode": "f2b5",
      "name": "Handshake Outlined",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.7000000000000002
    },
    {
      "id": "envelope-open",
      "unicode": "f2b6",
      "name": "Envelope Open",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.7000000000000002
    },
    {
      "id": "envelope-open-o",
      "unicode": "f2b7",
      "name": "Envelope Open Outlined",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.7000000000000002
    },
    {
      "name": "Linode",
      "unicode": "f2b8",
      "created": 4.7000000000000002,
      "url": "linode.com",
      "id": "linode",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "id": "address-book",
      "unicode": "f2b9",
      "name": "Address Book",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.7000000000000002
    },
    {
      "id": "address-book-o",
      "unicode": "f2ba",
      "name": "Address Book Outlined",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.7000000000000002
    },
    {
      "name": "Address Card",
      "unicode": "f2bb",
      "created": 4.7000000000000002,
      "id": "address-card",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "vcard"
      ]
    },
    {
      "name": "Address Card Outlined",
      "unicode": "f2bc",
      "created": 4.7000000000000002,
      "id": "address-card-o",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "vcard-o"
      ]
    },
    {
      "id": "user-circle",
      "unicode": "f2bd",
      "name": "User Circle",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.7000000000000002
    },
    {
      "id": "user-circle-o",
      "unicode": "f2be",
      "name": "User Circle Outlined",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.7000000000000002
    },
    {
      "id": "user-o",
      "unicode": "f2c0",
      "name": "User Outlined",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.7000000000000002
    },
    {
      "id": "id-badge",
      "unicode": "f2c1",
      "name": "Identification Badge",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.7000000000000002
    },
    {
      "name": "Identification Card",
      "unicode": "f2c2",
      "created": 4.7000000000000002,
      "id": "id-card",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "drivers-license"
      ]
    },
    {
      "name": "Identification Card Outlined",
      "unicode": "f2c3",
      "created": 4.7000000000000002,
      "id": "id-card-o",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "drivers-license-o"
      ]
    },
    {
      "id": "quora",
      "unicode": "f2c4",
      "name": "Quora",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.7000000000000002
    },
    {
      "id": "free-code-camp",
      "unicode": "f2c5",
      "name": "Free Code Camp",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.7000000000000002
    },
    {
      "id": "telegram",
      "unicode": "f2c6",
      "name": "Telegram",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.7000000000000002
    },
    {
      "name": "Thermometer Full",
      "unicode": "f2c7",
      "created": 4.7000000000000002,
      "id": "thermometer-full",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "thermometer-4",
        "thermometer"
      ]
    },
    {
      "name": "Thermometer 3/4 Full",
      "unicode": "f2c8",
      "created": 4.7000000000000002,
      "id": "thermometer-three-quarters",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "thermometer-3"
      ]
    },
    {
      "name": "Thermometer 1/2 Full",
      "unicode": "f2c9",
      "created": 4.7000000000000002,
      "id": "thermometer-half",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "thermometer-2"
      ]
    },
    {
      "name": "Thermometer 1/4 Full",
      "unicode": "f2ca",
      "created": 4.7000000000000002,
      "id": "thermometer-quarter",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "thermometer-1"
      ]
    },
    {
      "name": "Thermometer Empty",
      "unicode": "f2cb",
      "created": 4.7000000000000002,
      "id": "thermometer-empty",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "thermometer-0"
      ]
    },
    {
      "id": "shower",
      "unicode": "f2cc",
      "name": "Shower",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.7000000000000002
    },
    {
      "name": "Bath",
      "unicode": "f2cd",
      "created": 4.7000000000000002,
      "id": "bath",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "bathtub",
        "s15"
      ]
    },
    {
      "id": "podcast",
      "unicode": "f2ce",
      "name": "Podcast",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.7000000000000002
    },
    {
      "id": "window-maximize",
      "unicode": "f2d0",
      "name": "Window Maximize",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.7000000000000002
    },
    {
      "id": "window-minimize",
      "unicode": "f2d1",
      "name": "Window Minimize",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.7000000000000002
    },
    {
      "id": "window-restore",
      "unicode": "f2d2",
      "name": "Window Restore",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.7000000000000002
    },
    {
      "name": "Window Close",
      "unicode": "f2d3",
      "created": 4.7000000000000002,
      "id": "window-close",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "times-rectangle"
      ]
    },
    {
      "name": "Window Close Outline",
      "unicode": "f2d4",
      "created": 4.7000000000000002,
      "id": "window-close-o",
      "categories": [
        "Web Application Icons"
      ],
      "aliases": [
        "times-rectangle-o"
      ]
    },
    {
      "id": "bandcamp",
      "unicode": "f2d5",
      "name": "Bandcamp",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.7000000000000002
    },
    {
      "id": "grav",
      "unicode": "f2d6",
      "name": "Grav",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.7000000000000002
    },
    {
      "id": "etsy",
      "unicode": "f2d7",
      "name": "Etsy",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.7000000000000002
    },
    {
      "id": "imdb",
      "unicode": "f2d8",
      "name": "IMDB",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.7000000000000002
    },
    {
      "id": "ravelry",
      "unicode": "f2d9",
      "name": "Ravelry",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.7000000000000002
    },
    {
      "name": "Eercast",
      "unicode": "f2da",
      "created": 4.7000000000000002,
      "url": "eercast.com",
      "id": "eercast",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "id": "microchip",
      "unicode": "f2db",
      "name": "Microchip",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.7000000000000002
    },
    {
      "id": "snowflake-o",
      "unicode": "f2dc",
      "name": "Snowflake Outlined",
      "categories": [
        "Web Application Icons"
      ],
      "created": 4.7000000000000002
    },
    {
      "name": "Superpowers",
      "unicode": "f2dd",
      "created": 4.7000000000000002,
      "url": "superpowers.io",
      "id": "superpowers",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "name": "WPExplorer",
      "unicode": "f2de",
      "created": 4.7000000000000002,
      "url": "wpexplorer.com",
      "id": "wpexplorer",
      "categories": [
        "Brand Icons"
      ]
    },
    {
      "id": "meetup",
      "unicode": "f2e0",
      "name": "Meetup",
      "categories": [
        "Brand Icons"
      ],
      "created": 4.7000000000000002
    }
  ]
}
		';
		
	}
	

	
}