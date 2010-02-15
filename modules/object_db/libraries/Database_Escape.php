<?php defined('SYSPATH') or die('No direct script access.');

abstract class Database_Escape_Core {

	protected function escape($escape, $str)
	{
		if (is_array($str))
		{
			foreach ($str as $i => $val)
			{
				$str[$i] = $this->escape($escape, $val);
			}
			return '('.implode(', ', $str).')';
		}

		// Compile method name
		$escape = 'escape_'.$escape;

		if (is_object($str) AND $str instanceof Database_Select)
		{
			// Compile the sub-query
			$str = '('.$str->build().') AS '.$str->alias();
		}
		elseif (is_object($str) AND $str instanceof Database_Expression)
		{
			// Get the expression
			$str = $str->build();
		}

		if (preg_match('/^(.+)\s++AS\s++(.+)$/smi', trim($str), $matches))
		{
			if (strpos($matches[1], '(') === 0)
			{
				// Remove newlines in sub-queries
				$matches[1] = str_replace("\n", ' ', $matches[1]);
			}
			else
			{
				// Escape the string
				$matches[1] = $this->db->$escape($matches[1]);
			}

			// Escape the alias
			$matches[2] = $this->db->$escape($matches[2]);

			// Recompile the string
			$str = $matches[1].' AS '.$matches[2];
		}
		else
		{
			// Escape the string
			$str = $this->db->$escape($str);
		}

		return $str;
	}

} // End Database Escape