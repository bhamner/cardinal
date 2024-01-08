<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    //

    public function locationsByRectangulation($lat1,$lat2,$lng1,$lng2){

        return  $this->whereRaw("lat >= LEAST({$lat1},{$lat2})") 
                    ->whereRaw("lat <= GREATEST({$lat1},{$lat2})") 
                    ->whereRaw("lng >= LEAST({$lng1},{$lng2})") 
                    ->whereRaw("lng <= GREATEST({$lng1},{$lng2})") 
                    ->get();

    }
    public function locationsByRadius($lat,$lng,$distance){

        return  $this->selectRaw('*,'.
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
        
    
}
