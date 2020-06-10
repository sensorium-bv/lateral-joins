<?php

namespace Sensorium\LateralJoins\Grammars;

use Illuminate\Database\Query\Builder;
use Sensorium\LateralJoins\LateralJoinClause;

trait CompilesLateralJoins
{
	/**
	 *   Compile a lateral join
	 *   @param  Builder           $query [description]
	 *   @param  LateralJoinClause $join  [description]
	 *   @return [type]                   [description]
	 */
	public function compileLateralJoins(Builder $query, LateralJoinClause $join): string
	{
		$on = ($join->type === 'left') ? 'on true' : '';

		return "{$join->type} join {$join->table} $on";
	}

	/**
	 *   @override: compile lateral joins differently than regular joins
	 */
	protected function compileJoins(Builder $query, $joins)
    {
        return collect($joins)->map(function ($join) use ($query) {
        	if($join instanceof LateralJoinClause) {

        		return $this->compileLateralJoins($query, $join);

        	}else{
        		$table = $this->wrapTable($join->table);

	            $nestedJoins = is_null($join->joins) ? '' : ' '.$this->compileJoins($query, $join->joins);

	            $tableAndNestedJoins = is_null($join->joins) ? $table : '('.$table.$nestedJoins.')';

	            return trim("{$join->type} join {$tableAndNestedJoins} {$this->compileWheres($join)}");
        	}
        })->implode(' ');
    }
}