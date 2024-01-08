<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zip extends Model
{
    protected $guarded = [];

    // Zips in specified distance by Haversine formula

    public static function zipsByDistance($lat, $lng, $distance)
    {
        return  Zip::selectRaw('*,'.
            sprintf('ROUND(( 3959 * acos(
              cos( radians(%s) )
            * cos( radians( zips.lat ) )
            * cos( radians( zips.lng ) - radians(%s) )
            + sin( radians(%s) )
            * sin(radians(zips.lat))
          )))', $lat, $lng, $lat).'as distance')
            ->havingRaw('distance < '.$distance)
            ->orderBy('zip')->get();
    }
    
    // Zips in rectangular area
    
    public static function zipsByRectangulation($lat1, $lat2, $lng1, $lng2)
    {
        return  Zip::whereRaw("lat >= LEAST({$lat1},{$lat2})")
                    ->whereRaw("lat <= GREATEST({$lat1},{$lat2})")
                    ->whereRaw("lng >= LEAST({$lng1},{$lng2})")
                    ->whereRaw("lng <= GREATEST({$lng1},{$lng2})")
                    ->get();
    }


    public static function zipsByPrefix($zips)
    {
        return Zip::where(function($query) use ($zips) {
            foreach($zips as $value){
                    $query->orWhere('zip','like',$value."%"); 
                }
        })->get();


    }

}
