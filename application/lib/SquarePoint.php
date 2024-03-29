<?php 
    namespace app\lib;
    
    trait SquarePoint
    {  
       protected static $EARTH_RADIUS;
       
       protected static function getPoint($data = [])
       {
           self::$EARTH_RADIUS = 6371;//地球半径，平均半径为6371km
           
           return self::mathPoint($data[0], $data[1], $data[2]);
       }

        /**计算某个经纬度的周围某段距离的正方形的四个点
         *
         * @param $lng float 经度
         * @param $lat float 纬度
         * @param $distance float 该点所在圆的半径，该圆与此正方形内切，默认值为0.5千米
         * @return array 正方形的四个点的经纬度坐标
         */
        protected static function mathPoint($lng, $lat,$distance = 2)
        {
                
            $dlng =  2 * asin(sin($distance / (2 * self::$EARTH_RADIUS)) / cos(deg2rad($lat)));
            $dlng = rad2deg($dlng);
            
            $dlat = $distance/self::$EARTH_RADIUS;
            $dlat = rad2deg($dlat);
            
            return array(
                'left-top'=>array('lat'=>$lat + $dlat,'lng'=>$lng-$dlng),
                'right-top'=>array('lat'=>$lat + $dlat, 'lng'=>$lng + $dlng),
                'left-bottom'=>array('lat'=>$lat - $dlat, 'lng'=>$lng - $dlng),
                'right-bottom'=>array('lat'=>$lat - $dlat, 'lng'=>$lng + $dlng)
            );
        }
    }