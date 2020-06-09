<?php

namespace Sensorium\LateralCte;

//use Illuminate\Database\Query\Builder as Base;
use Illuminate\Database\Query\Expression;
use Staudenmeir\LaravelCte\Query\Builder as Base;

class Builder extends Base
{
	public function joinLateral($query, $as, $type = 'cross')
    {
        [$query, $bindings] = $this->createSub($query);

        $expression = 'lateral ('.$query.') as '.$this->grammar->wrapTable($as);

        $join = $this->newLateralCteClause($this, $type, new Expression($expression));

        $this->joins[] = $join;

        $this->addBinding($bindings, 'join');

        return $this;
    }

    public function leftJoinLateral($query, $as)
    {
    	return $this->joinLateral($query, $as, 'left');
    }

    protected function newLateralCteClause(self $parentQuery, $type, $table)
    {
        return new LateralJoinClause($parentQuery, $type, $table);
    }
}