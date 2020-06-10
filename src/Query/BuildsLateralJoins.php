<?php

namespace Sensorium\LateralJoins\Query;

use Illuminate\Database\Query\Expression;
use Sensorium\LateralJoins\LateralJoinClause;

trait BuildsLateralJoins
{
	public function joinLateral($query, $as, $type = 'cross')
    {
        [$query, $bindings] = $this->createSub($query);

        $expression = 'lateral ('.$query.') as '.$this->grammar->wrapTable($as);

        $join = $this->newLateralJoinsClause($this, $type, new Expression($expression));

        $this->joins[] = $join;

        $this->addBinding($bindings, 'join');

        return $this;
    }

    public function leftJoinLateral($query, $as)
    {
    	return $this->joinLateral($query, $as, 'left');
    }

    protected function newLateralJoinsClause(self $parentQuery, $type, $table)
    {
        return new LateralJoinClause($parentQuery, $type, $table);
    }
}