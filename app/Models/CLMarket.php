<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CLMarket extends Model
{
    protected $table = "craigslist_markets";
    protected $guarded = [];
    
    // Markets in specified radius by Haversine formula

    public static function marketsByDistance($lat,$lng,$distance){

        return  CLMarket::selectRaw('*,'.
        sprintf('ROUND(( 3959 * acos(
          cos( radians(%s) )
        * cos( radians( craigslist_markets.lat ) )
        * cos( radians( craigslist_markets.lng ) - radians(%s) )
        + sin( radians(%s) )
        * sin(radians(craigslist_markets.lat))
      )))', $lat, $lng, $lat).'as distance')
        ->havingRaw('distance < '.$distance)
        ->orderBy('distance')->get();
    }

    // Markets in specified rectangular area

    public static function marketsByRectangulation($lat1,$lat2,$lng1,$lng2){

        return  CLMarket::whereRaw("lat >= LEAST({$lat1},{$lat2})") 
                    ->whereRaw("lat <= GREATEST({$lat1},{$lat2})") 
                    ->whereRaw("lng >= LEAST({$lng1},{$lng2})") 
                    ->whereRaw("lng <= GREATEST({$lng1},{$lng2})") 
                    ->get();

    }
   
}
