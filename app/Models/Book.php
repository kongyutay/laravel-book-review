<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    public function reviews() {
        return $this->hasMany(Review::class);
    }

    public function scopeTitle(Builder $query, string $title): Builder {
        return $query->where("title","like","%". $title ."%");
    }

    // 作用域方法定义的时候要加scope前缀，但是laravel会自动创建popular方法这是一个命名约定，例如\App\Models\Book::popular()->highestRated()->get()
    // 调用的时候不用给popular传递参数因为已经把Builder这个query构造器传入（依赖注入）了，laravel会自动处理
    public function scopePopular(Builder $query, $from = null, $to = null): Builder|QueryBuilder {
        return $query->withCount(['reviews' => fn (Builder $q) => $this->dateRangeFilter($q, $from, $to)])->orderBy('reviews_count','desc');
    }

    public function scopeHighestRated(Builder $query, $from = null, $to = null): Builder|QueryBuilder {
        return $query->withAvg(['reviews' => fn (Builder $q) => $this->dateRangeFilter($q, $from, $to)], 'rating')->orderBy('reviews_avg_rating', 'desc');
    }

    public function scopeMinReviews(Builder $query, int $minReviews):Builder|QueryBuilder {
        return $query->having('reviews_count','>=', $minReviews);
    }

    private function dateRangeFilter(Builder $query, $from = null, $to = null) {
        if( $from && !$to){
            $query->where('created_at','>=','$from');
        } elseif( !$from && $to ){
            $query->where('created_at','<=','$to');
        } elseif( $from && $to ){
            $query->where('created_at', [$from, $to]);
        }
    }
}
