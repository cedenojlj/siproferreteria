<?php

namespace App\Traits;

trait BelongsToCompany
{
    protected static function bootBelongsToCompany()
    {
        static::addGlobalScope('company', function ($query) {
            if (auth()->check()) {
                $query->where('company_id', auth()->user()->company_id);
            }
        });
        
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->company_id = auth()->user()->company_id;
            }
        });
    }
}