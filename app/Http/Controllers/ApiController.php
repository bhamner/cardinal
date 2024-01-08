<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;

use App\Models\Zip;
use App\Models\Lead;
use App\Models\CLMarket;
use App\Models\HiringData;

use App\Http\Helpers\Helper;
use App\Http\Helpers\Distance;

class ApiController extends Controller
{
 

    /**
     * Get locations from radius.
     *
     * @return void
     */
    public function nearbyZips(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'location' => 'required',
            'radius' => 'required|numeric|max:150',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $zip = explode(',', trim($request->location, '()'));
        
        $coordinate = self::coordinateFromZip($zip[0]);

        if ($coordinate == false) {
            $error = array(
                'status' => 'Error',
                'message' => 'Could not retrieve location data for given zip code'
            );
            return response()->json($error, 422);
        }

        $nearbyZips = Zip::zipsByDistance($coordinate->lat, $coordinate->lng, $request->radius);

        return response()->json($nearbyZips);
    }

    /**
     * Get craigslist market locations from radius.
     *
     * @return void
     */
    public function nearbyCLMarkets(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'location' => 'required',
            'radius' => 'required|numeric|max:150',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $zip = trim($request->location, '()');

        $coordinate = self::coordinateFromZip($zip);

        if ($coordinate == false) {
            $error = array(
                'status' => 'Error',
                'message' => 'Could not retrieve location data for given zip code'
            );
            return response()->json($error, 422);
        }

        $nearbyMarkets = CLMarket::marketsByDistance($coordinate->lat, $coordinate->lng, $request->radius);

        return response()->json($nearbyMarkets);
    }




    /**
     * Get locations from boundary.
     *
     * @return void
     */
    public function zipsInBoundary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'location' => 'required',
        ]);
        
        // Ensure coordinated entered
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $coords = explode(',', trim($request->location, '()'));

        if (count($coords) != 4) {
            $error = array(
                'status' => 'Incorrect number of coordinates',
                'message' => 'Must contain 4 comma-seperated coordinates'
            );
            return response()->json($error, 422);
        }

        $containedZips = Zip::zipsByRectangulation($coords[0], $coords[1], $coords[2], $coords[3]);


        return response()->json($containedZips);
    }


    /**
     * Get markets from boundary.
     *
     * @return void
     */
    public function marketsInBoundary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'location' => 'required',
        ]);

        // Ensure coordinated entered
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $coords = explode(',', trim($request->location, '()'));

        if (count($coords) != 4) {
            $error = array(
                'status' => 'Incorrect number of coordinates',
                'message' => 'Must contain 4 comma-seperated coordinates'
            );
            return response()->json($error, 422);
        }

        $containedMarkets = CLMarket::marketsByRectangulation($coords[0], $coords[1], $coords[2], $coords[3]);


        return response()->json($containedMarkets);
    }



    /**
     * Get markets on Route.
     *
     * @return void
     */
    public function marketsOnRoute(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'location' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $zips = explode(',', trim($request->location, '()'));

        $coordinates = self::getCoordinates($zips);

        if ($coordinates == false) {
            $error = array(
                'status' => "INVALID_REQUEST",
                'message' => "Request incorrectly formatted"
            );
            return response()->json($error, 422);
        }

        $routeMarkets = collect($coordinates)->flatMap(function ($coord) {
            return CLMarket::marketsByDistance($coord->lat, $coord->lng, 20);
        })->unique('city')->values()->all();

        return response()->json($routeMarkets);
    }



    /**
     * Get zips on Route.
     *
     * @return void
     */
    public function zipsOnRoute(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'location' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $zips = explode(',', trim($request->location, '()'));

        $coordinates = self::getCoordinates($zips);

        if ($coordinates == false) {
            $error = array(
                'status' => "INVALID_REQUEST",
                'message' => "Request incorrectly formatted"
            );
            return response()->json($error, 422);
        }

        $routeZips = collect($coordinates)->flatMap(function ($coord) {
            return Zip::zipsByDistance($coord->lat, $coord->lng, 5);
        })->unique('zip')->values()->all();

        return response()->json($routeZips);
    }


    /**
     * Retrieve coordinates from google geocoder api
     *
     * @return array
     */
    private function getCoordinates($zips)
    {
    
        // Route origin
        $origin = $zips[0];

        //Route destination
        $destination = end($zips);

        // Do not include origin an destination in waypoints
        $waypoints = array_slice($zips, 1, count($zips)-2);
        
        // Comma seperated waypoins
        $strWaypoints = implode(",", $waypoints);

        // Build URL
        $prefix = "https://maps.googleapis.com/maps/api/directions/json?";
        $url =  $prefix."origin=".$origin."&destination=".$destination."&waypoints=".$strWaypoints."&key=".env('GOOGLE_MAPS_BROWSER_KEY');

        // Retrieve route coordinates from gmaps
        $response = collect(json_decode(Helper::fetchApiDataFrom($url)));

        if ($response["status"] != "OK") {
            return false;
        }
        
        $routes =  collect($response["routes"])->flatten()->pluck("legs");
        $steps = array_column($routes->toArray()[0], 'steps');
        $steps = array_flatten($steps);

        // Array of coordinates
        $coordinates = array_map(function ($values) {
            $lat = $values->start_location->lat;
            $lng = $values->start_location->lng;
            
            return (object)['lat' => $lat, 'lng' => $lng];
        }, $steps);
        
        $reducedCoordinates = self::reduceCoordinatesByMiles($coordinates, 20);

        return $reducedCoordinates;
    }

    /**
     * Reduce coordinate of givin zip
     *
     * @return object
     */
    private function coordinateFromZip($zip)
    {
        $prefix = "https://maps.googleapis.com/maps/api/geocode/json?address=";
        $url =  $prefix."address=".$zip."&key=".env('GOOGLE_MAPS_BROWSER_KEY');
        $response = json_decode(Helper::fetchApiDataFrom($url));
        $status = $response->status;
        
        if ($response->status != "OK") {
            return false;
        }
        
        $coordinate = $response->results[0]->geometry->location;

        return $coordinate;
    }
    

    /**
    * Reduce amount of coordinates by the specified distance
    *
    * @return array
    */
    private function reduceCoordinatesByMiles($coordinates, $miles)
    {
        $lastCoordinate = null;
        $reducedCoordinates = [];
        foreach ($coordinates as $coordinate) {
            // Keep first, last and coordinates with distance >= miles
            if (current($coordinates) == $coordinate
                || Distance::distance($lastCoordinate, $coordinate) >= $miles
                || end($coordinates) == $coordinate) {
                $lastCoordinate = $coordinate;
                array_push($reducedCoordinates, $coordinate);
            }
        }
        return $reducedCoordinates;
    }
}
