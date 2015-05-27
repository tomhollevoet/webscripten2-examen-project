<?php

namespace ImmoTom\Repository;

class SiteRepository extends \Knp\Repository {

	public function getTableName() {
		return 'immotom';
	}
        
        public function getBrokersStart() {
                return $this->db->fetchAll('SELECT brokers.idbrokers, brokers.name, brokers.image, brokers.address, brokers.zip, brokers.city, brokers.phone from brokers');
        }
        
        public function getRealeStateStart() {
                return $this->db->fetchAll('SELECT 
                    realestate.idrealestate, realestate.forsale, realestate.soldrented, category.name, photos.photoname, realestate.price, realestate.city, realestate.countclick, realestate.price 
                    FROM realestate 
                    INNER JOIN photos ON realestate.idrealestate = photos.idrealestate 
                    INNER JOIN category ON category.idcategory = realestate.category 
                    WHERE photos.firstimage = 1 AND  realestate.soldrented = 0
                    ORDER BY realestate.countclick 
                    DESC LIMIT 0,4');
        }
        
        public function getRealeState($id, $forsale) {
                return $this->db->fetchAssoc('SELECT 
                    realestate.idrealestate, realestate.title, realestate.address, realestate.zip, realestate.city, realestate.price,
                    realestate.definition, realestate.detail, realestate.division, realestate.broker, realestate.soldrented, 
                    realestate.countclick, realestate.forsale, realestate.category, category.name
                    FROM realestate
                    INNER JOIN category ON category.idcategory = realestate.category 
                    WHERE realestate.idrealestate = ? AND realestate.forsale = ?', array($id, $forsale));
        }
        
        public function getRealeStateContact($id) {
                return $this->db->fetchAssoc('SELECT 
                    realestate.idrealestate, realestate.title, realestate.address, realestate.zip, realestate.city, realestate.price,
                    realestate.definition, realestate.detail, realestate.division, realestate.broker, realestate.soldrented, 
                    realestate.countclick, realestate.forsale, realestate.category, category.name
                    FROM realestate
                    INNER JOIN category ON category.idcategory = realestate.category 
                    WHERE realestate.idrealestate = ?', array($id));
        }
        
        
        public function getBroker($id) {
                return $this->db->fetchAssoc('SELECT 
                    idbrokers, name, address, zip, city, email, phone, image
                    FROM brokers
                    WHERE idbrokers = ?', array($id));
        }
        
        public function getPhotosRealeState($id) {
                return $this->db->fetchAll('SELECT 
                    photoname, firstimage
                    FROM photos
                    WHERE idrealestate = ?', array($id));
        }
        
        public function getRelatedRealeStates($id, $category, $forsale) {
                return $this->db->fetchAll('SELECT 
                    realestate.idrealestate, realestate.forsale, realestate.soldrented, category.name, photos.photoname, 
                    realestate.price, realestate.city, realestate.countclick, realestate.price, realestate.category 
                    FROM realestate 
                    INNER JOIN photos ON realestate.idrealestate = photos.idrealestate 
                    INNER JOIN category ON category.idcategory = realestate.category 
                    WHERE photos.firstimage = 1 AND realestate.soldrented = 0 AND realestate.category = ? AND realestate.forsale = ? AND realestate.idrealestate != ? AND realestate.visible = 1
                    ORDER BY realestate.countclick
                    DESC LIMIT 0,5', array($category, $forsale, $id));
        }
        
        public function getCountClick($id) {
                return $this->db->fetchAssoc('SELECT 
                    countclick
                    FROM realestate
                    WHERE idrealestate = ?', array($id));
        }
        
        public function editCountClick($id, $countClick) {
                $this->db->executeQuery('UPDATE 
                        realestate
                        SET countclick = ?
                        WHERE idrealestate = ?', 
                        array($countClick, $id));
        }
        
        public function getRealeStates($forsale, $begin, $end, $category, $minprice, $maxprice, $bedroom) {
            $begin = (int)$begin;
            $end = (int)$end;
            
            if(is_numeric($begin) == false || is_numeric($end) == false){
                return null;
            }
            
            // 0) No filter
            if ($category == null && $minprice == null && $maxprice == null && $bedroom == null){
                return $this->db->fetchAll('SELECT 
                    realestate.idrealestate, realestate.forsale, realestate.soldrented, category.name, 
                    photos.photoname, realestate.price, realestate.city, realestate.countclick, realestate.price, realestate.bedrooms, realestate.category 
                    FROM realestate 
                    INNER JOIN photos ON realestate.idrealestate = photos.idrealestate 
                    INNER JOIN category ON category.idcategory = realestate.category 
                    WHERE photos.firstimage = 1 AND realestate.forsale = ? AND realestate.visible = 1
                    ORDER BY realestate.countclick
                    DESC LIMIT ' . $begin . ',' . $end . '', array($forsale));
            }
            
            // 1) Only category
            if($category != null && $minprice == null && $maxprice == null && $bedroom == null){
                return $this->db->fetchAll('SELECT 
                    realestate.idrealestate, realestate.forsale, realestate.soldrented, category.name, 
                    photos.photoname, realestate.price, realestate.city, realestate.countclick, realestate.price, realestate.bedrooms, realestate.category 
                    FROM realestate 
                    INNER JOIN photos ON realestate.idrealestate = photos.idrealestate 
                    INNER JOIN category ON category.idcategory = realestate.category 
                    WHERE photos.firstimage = 1 AND realestate.forsale = ? AND realestate.category = ? AND realestate.visible = 1
                    ORDER BY realestate.countclick
                    DESC LIMIT ' . $begin . ',' . $end . '', array($forsale, $category));
            }
            
            // 2) Only min price
            if($category == null && $minprice != null && $maxprice == null && $bedroom == null){
                return $this->db->fetchAll('SELECT 
                    realestate.idrealestate, realestate.forsale, realestate.soldrented, category.name, 
                    photos.photoname, realestate.price, realestate.city, realestate.countclick, realestate.price, realestate.bedrooms, realestate.category 
                    FROM realestate 
                    INNER JOIN photos ON realestate.idrealestate = photos.idrealestate 
                    INNER JOIN category ON category.idcategory = realestate.category 
                    WHERE photos.firstimage = 1 AND realestate.forsale = ? AND realestate.price >= ? AND realestate.visible = 1
                    ORDER BY realestate.countclick
                    DESC LIMIT ' . $begin . ',' . $end . '', array($forsale, $minprice));
            }
            
            // 3) Only max price
            if($category == null && $minprice == null && $maxprice != null && $bedroom == null){
                return $this->db->fetchAll('SELECT 
                    realestate.idrealestate, realestate.forsale, realestate.soldrented, category.name, 
                    photos.photoname, realestate.price, realestate.city, realestate.countclick, realestate.price, realestate.bedrooms, realestate.category 
                    FROM realestate 
                    INNER JOIN photos ON realestate.idrealestate = photos.idrealestate 
                    INNER JOIN category ON category.idcategory = realestate.category 
                    WHERE photos.firstimage = 1 AND realestate.forsale = ? AND realestate.price <= ? AND realestate.visible = 1
                    ORDER BY realestate.countclick
                    DESC LIMIT ' . $begin . ',' . $end . '', array($forsale, $maxprice));
            }
            
            // 4) Only bedroom
            if($category == null && $minprice == null && $maxprice == null && $bedroom != null){
                return $this->db->fetchAll('SELECT 
                    realestate.idrealestate, realestate.forsale, realestate.soldrented, category.name, 
                    photos.photoname, realestate.price, realestate.city, realestate.countclick, realestate.price, realestate.bedrooms, realestate.category 
                    FROM realestate 
                    INNER JOIN photos ON realestate.idrealestate = photos.idrealestate 
                    INNER JOIN category ON category.idcategory = realestate.category 
                    WHERE photos.firstimage = 1 AND realestate.forsale = ? AND realestate.bedrooms = ? AND realestate.visible = 1
                    ORDER BY realestate.countclick
                    DESC LIMIT ' . $begin . ',' . $end . '', array($forsale, $bedroom));
            }
            
            // 5) category AND min price
            if($category != null && $minprice != null && $maxprice == null && $bedroom == null){
                return $this->db->fetchAll('SELECT 
                    realestate.idrealestate, realestate.forsale, realestate.soldrented, category.name, 
                    photos.photoname, realestate.price, realestate.city, realestate.countclick, realestate.price, realestate.bedrooms, realestate.category 
                    FROM realestate 
                    INNER JOIN photos ON realestate.idrealestate = photos.idrealestate 
                    INNER JOIN category ON category.idcategory = realestate.category 
                    WHERE photos.firstimage = 1 AND realestate.forsale = ? AND realestate.category = ? AND realestate.price >= ? AND realestate.visible = 1
                    ORDER BY realestate.countclick
                    DESC LIMIT ' . $begin . ',' . $end . '', array($forsale, $category, $minprice));
            }
            
            // 6) category AND max price
            if($category != null && $minprice == null && $maxprice != null && $bedroom == null){
                return $this->db->fetchAll('SELECT 
                    realestate.idrealestate, realestate.forsale, realestate.soldrented, category.name, 
                    photos.photoname, realestate.price, realestate.city, realestate.countclick, realestate.price, realestate.bedrooms, realestate.category 
                    FROM realestate 
                    INNER JOIN photos ON realestate.idrealestate = photos.idrealestate 
                    INNER JOIN category ON category.idcategory = realestate.category 
                    WHERE photos.firstimage = 1 AND realestate.forsale = ? AND realestate.category = ? AND realestate.price <= ? AND realestate.visible = 1
                    ORDER BY realestate.countclick
                    DESC LIMIT ' . $begin . ',' . $end . '', array($forsale, $category, $maxprice));
            }
            
            // 7) category AND bedrooms
            if($category != null && $minprice == null && $maxprice == null && $bedroom != null){
                return $this->db->fetchAll('SELECT 
                    realestate.idrealestate, realestate.forsale, realestate.soldrented, category.name, 
                    photos.photoname, realestate.price, realestate.city, realestate.countclick, realestate.price, realestate.bedrooms, realestate.category 
                    FROM realestate 
                    INNER JOIN photos ON realestate.idrealestate = photos.idrealestate 
                    INNER JOIN category ON category.idcategory = realestate.category 
                    WHERE photos.firstimage = 1 AND realestate.forsale = ? AND realestate.category = ? AND realestate.bedrooms = ? AND realestate.visible = 1
                    ORDER BY realestate.countclick
                    DESC LIMIT ' . $begin . ',' . $end . '', array($forsale, $category, $bedroom));
            }
            
            // 8) min price and max price
            if($category == null && $minprice != null && $maxprice != null && $bedroom == null){
                return $this->db->fetchAll('SELECT 
                    realestate.idrealestate, realestate.forsale, realestate.soldrented, category.name, 
                    photos.photoname, realestate.price, realestate.city, realestate.countclick, realestate.price, realestate.bedrooms, realestate.category 
                    FROM realestate 
                    INNER JOIN photos ON realestate.idrealestate = photos.idrealestate 
                    INNER JOIN category ON category.idcategory = realestate.category 
                    WHERE photos.firstimage = 1 AND realestate.forsale = ? AND realestate.price >= ? AND realestate.price <= ? AND realestate.visible = 1
                    ORDER BY realestate.countclick
                    DESC LIMIT ' . $begin . ',' . $end . '', array($forsale, $minprice, $maxprice));
            }
            
            // 9) min price AND bedrooms
            if($category == null && $minprice != null && $maxprice == null && $bedroom != null){
                return $this->db->fetchAll('SELECT 
                    realestate.idrealestate, realestate.forsale, realestate.soldrented, category.name, 
                    photos.photoname, realestate.price, realestate.city, realestate.countclick, realestate.price, realestate.bedrooms, realestate.category 
                    FROM realestate 
                    INNER JOIN photos ON realestate.idrealestate = photos.idrealestate 
                    INNER JOIN category ON category.idcategory = realestate.category 
                    WHERE photos.firstimage = 1 AND realestate.forsale = ? AND realestate.price >= ? AND realestate.bedrooms = ? AND realestate.visible = 1
                    ORDER BY realestate.countclick
                    DESC LIMIT ' . $begin . ',' . $end . '', array($forsale, $minprice, $bedroom));
            }
            
            // 10) max price AND bedrooms
            if($category == null && $minprice == null && $maxprice != null && $bedroom != null){
                return $this->db->fetchAll('SELECT 
                    realestate.idrealestate, realestate.forsale, realestate.soldrented, category.name, 
                    photos.photoname, realestate.price, realestate.city, realestate.countclick, realestate.price, realestate.bedrooms, realestate.category 
                    FROM realestate 
                    INNER JOIN photos ON realestate.idrealestate = photos.idrealestate 
                    INNER JOIN category ON category.idcategory = realestate.category 
                    WHERE photos.firstimage = 1 AND realestate.forsale = ? AND realestate.price <= ? AND realestate.bedrooms = ? AND realestate.visible = 1
                    ORDER BY realestate.countclick
                    DESC LIMIT ' . $begin . ',' . $end . '', array($forsale, $maxprice, $bedroom));
            }
            
            // 11) max price and bedrooms
            if($category != null && $minprice != null && $maxprice != null && $bedroom == null){
                return $this->db->fetchAll('SELECT 
                    realestate.idrealestate, realestate.forsale, realestate.soldrented, category.name, 
                    photos.photoname, realestate.price, realestate.city, realestate.countclick, realestate.price, realestate.bedrooms, realestate.category 
                    FROM realestate 
                    INNER JOIN photos ON realestate.idrealestate = photos.idrealestate 
                    INNER JOIN category ON category.idcategory = realestate.category 
                    WHERE photos.firstimage = 1 AND realestate.forsale = ? and realestate.category = ? AND realestate.price >= ? AND realestate.price <= ? AND realestate.visible = 1
                    ORDER BY realestate.countclick
                    DESC LIMIT ' . $begin . ',' . $end . '', array($forsale, $category, $minprice, $maxprice));
            }
            
            // 12) category AND max price AND bedroom
            if($category != null && $minprice == null && $maxprice != null && $bedroom != null){
                return $this->db->fetchAll('SELECT 
                    realestate.idrealestate, realestate.forsale, realestate.soldrented, category.name, 
                    photos.photoname, realestate.price, realestate.city, realestate.countclick, realestate.price, realestate.bedrooms, realestate.category 
                    FROM realestate 
                    INNER JOIN photos ON realestate.idrealestate = photos.idrealestate 
                    INNER JOIN category ON category.idcategory = realestate.category 
                    WHERE photos.firstimage = 1 AND realestate.forsale = ? and realestate.category = ? AND realestate.price <= ? AND realestate.bedrooms = ? AND realestate.visible = 1
                    ORDER BY realestate.countclick
                    DESC LIMIT ' . $begin . ',' . $end . '', array($forsale, $category, $maxprice, $bedroom));
            }
            
            // 13) min price AND max price AND bedroom
            if($category == null && $minprice != null && $maxprice != null && $bedroom != null){
                return $this->db->fetchAll('SELECT 
                    realestate.idrealestate, realestate.forsale, realestate.soldrented, category.name, 
                    photos.photoname, realestate.price, realestate.city, realestate.countclick, realestate.price, realestate.bedrooms, realestate.category 
                    FROM realestate 
                    INNER JOIN photos ON realestate.idrealestate = photos.idrealestate 
                    INNER JOIN category ON category.idcategory = realestate.category 
                    WHERE photos.firstimage = 1 AND realestate.forsale = ? and realestate.price >= ? AND realestate.price <= ? AND realestate.bedrooms = ? AND realestate.visible = 1
                    ORDER BY realestate.countclick
                    DESC LIMIT ' . $begin . ',' . $end . '', array($forsale, $minprice, $maxprice, $bedroom));
            }
            
            // 14) category AND min price AND bedroom
            if($category != null && $minprice != null && $maxprice == null && $bedroom != null){
                return $this->db->fetchAll('SELECT 
                    realestate.idrealestate, realestate.forsale, realestate.soldrented, category.name, 
                    photos.photoname, realestate.price, realestate.city, realestate.countclick, realestate.price, realestate.bedrooms, realestate.category 
                    FROM realestate 
                    INNER JOIN photos ON realestate.idrealestate = photos.idrealestate 
                    INNER JOIN category ON category.idcategory = realestate.category 
                    WHERE photos.firstimage = 1 AND realestate.forsale = ? and realestate.category = ? AND realestate.price >= ? AND realestate.bedrooms = ? AND realestate.visible = 1
                    ORDER BY realestate.countclick
                    DESC LIMIT ' . $begin . ',' . $end . '', array($forsale, $category, $minprice, $bedroom));
            }
            
            // 15) ALL
            if($category != null && $minprice != null && $maxprice != null && $bedroom != null){
                return $this->db->fetchAll('SELECT 
                    realestate.idrealestate, realestate.forsale, realestate.soldrented, category.name, 
                    photos.photoname, realestate.price, realestate.city, realestate.countclick, realestate.price, realestate.bedrooms, realestate.category 
                    FROM realestate 
                    INNER JOIN photos ON realestate.idrealestate = photos.idrealestate 
                    INNER JOIN category ON category.idcategory = realestate.category 
                    WHERE photos.firstimage = 1 AND realestate.forsale = ? and realestate.category = ? AND realestate.price >= ? AND realestate.price <= ? AND realestate.bedrooms = ? AND realestate.visible = 1
                    ORDER BY realestate.countclick
                    DESC LIMIT ' . $begin . ',' . $end . '', array($forsale, $category, $minprice, $maxprice, $bedroom));
            }
        }
        
        public function countRealeStates($forsale, $category, $minprice, $maxprice, $bedroom) {
            
            // 0) No filter
            if ($category == null && $minprice == null && $maxprice == null && $bedroom == null){
                return $this->db->fetchAssoc('SELECT COUNT(idrealestate) from realestate WHERE realestate.forsale = ? AND realestate.visible = 1', array($forsale));
            }
            
            // 1) Only category
            if($category != null && $minprice == null && $maxprice == null && $bedroom == null){
                return $this->db->fetchAssoc('SELECT COUNT(idrealestate) from realestate WHERE realestate.forsale = ? AND realestate.category = ? AND realestate.visible = 1', array($forsale, $category));
            }
            
            // 2) Only min price
            if($category == null && $minprice != null && $maxprice == null && $bedroom == null){
                return $this->db->fetchAssoc('SELECT COUNT(idrealestate) from realestate WHERE realestate.forsale = ? AND realestate.price >= ? AND realestate.visible = 1', array($forsale, $minprice));
            }
            
            // 3) Only max price
            if($category == null && $minprice == null && $maxprice != null && $bedroom == null){
                return $this->db->fetchAssoc('SELECT COUNT(idrealestate) from realestate WHERE realestate.forsale = ? AND realestate.price <= ? AND realestate.visible = 1', array($forsale, $maxprice));
            }
            
            // 4) Only bedroom
            if($category == null && $minprice == null && $maxprice == null && $bedroom != null){
                return $this->db->fetchAssoc('SELECT COUNT(idrealestate) from realestate WHERE realestate.forsale = ? AND realestate.bedrooms = ? AND realestate.visible = 1', array($forsale, $bedroom));
            }
            
            // 5) category AND min price
            if($category != null && $minprice != null && $maxprice == null && $bedroom == null){
                return $this->db->fetchAssoc('SELECT COUNT(idrealestate) from realestate WHERE realestate.forsale = ? AND realestate.category = ? AND realestate.price >= ? AND realestate.visible = 1', array($forsale, $category, $minprice));
            }
            
            // 6) category AND max price
            if($category != null && $minprice == null && $maxprice != null && $bedroom == null){
                return $this->db->fetchAssoc('SELECT COUNT(idrealestate) from realestate WHERE realestate.forsale = ? AND realestate.category = ? AND realestate.price <= ? AND realestate.visible = 1', array($forsale, $category, $maxprice));
            }
            
            // 7) category AND bedrooms
            if($category != null && $minprice == null && $maxprice == null && $bedroom != null){
                return $this->db->fetchAssoc('SELECT COUNT(idrealestate) from realestate WHERE realestate.forsale = ? AND realestate.category = ? AND realestate.bedrooms = ? AND realestate.visible = 1', array($forsale, $category, $bedroom));
            }
            
            // 8) min price AND max price
            if($category == null && $minprice != null && $maxprice != null && $bedroom == null){
                return $this->db->fetchAssoc('SELECT COUNT(idrealestate) from realestate WHERE realestate.forsale = ? AND realestate.price >= ? AND realestate.price <= ? AND realestate.visible = 1', array($forsale, $minprice, $maxprice));
            }
            
            // 9) min price AND bedrooms
            if($category == null && $minprice != null && $maxprice == null && $bedroom != null){
                return $this->db->fetchAssoc('SELECT COUNT(idrealestate) from realestate WHERE realestate.forsale = ? AND realestate.price >= ? AND realestate.bedrooms = ? AND realestate.visible = 1', array($forsale, $minprice, $bedroom));
            }
            
            // 10) max price AND bedrooms
            if($category == null && $minprice == null && $maxprice != null && $bedroom != null){
                return $this->db->fetchAssoc('SELECT COUNT(idrealestate) from realestate WHERE realestate.forsale = ? AND realestate.price <= ? AND realestate.bedrooms = ? AND realestate.visible = 1', array($forsale, $maxprice, $bedroom));
            }
            
            // 11) category AND min price AND max price
            if($category != null && $minprice != null && $maxprice != null && $bedroom == null){
                return $this->db->fetchAssoc('SELECT COUNT(idrealestate) from realestate WHERE realestate.forsale = ? AND realestate.category = ? AND realestate.price >= ? AND realestate.price <= ? AND realestate.visible = 1', array($forsale, $category, $minprice, $maxprice));
            }
            
            // 12) category AND max price AND bedroom
            if($category != null && $minprice == null && $maxprice != null && $bedroom != null){
                return $this->db->fetchAssoc('SELECT COUNT(idrealestate) from realestate WHERE realestate.forsale = ? AND realestate.category = ? AND realestate.price <= ? AND realestate.bedrooms = ? AND realestate.visible = 1', array($forsale, $category, $maxprice, $bedroom));
            }
            
            // 13) min price AND max price AND bedroom
            if($category == null && $minprice != null && $maxprice != null && $bedroom != null){
                return $this->db->fetchAssoc('SELECT COUNT(idrealestate) from realestate WHERE realestate.forsale = ? AND realestate.price >= ? AND realestate.price <= ? AND realestate.bedrooms = ? AND realestate.visible = 1', array($forsale, $minprice, $maxprice, $bedroom));
            }
            
            // 14) category AND min price AND bedroom
            if($category != null && $minprice != null && $maxprice == null && $bedroom != null){
                return $this->db->fetchAssoc('SELECT COUNT(idrealestate) from realestate WHERE realestate.forsale = ? AND realestate.category = ? AND realestate.price >= ? AND realestate.bedrooms = ? AND realestate.visible = 1', array($forsale, $category, $minprice, $bedroom));
            }
            
            // 15) ALL
            if($category != null && $minprice != null && $maxprice != null && $bedroom != null){
                return $this->db->fetchAssoc('SELECT COUNT(idrealestate) from realestate WHERE realestate.forsale = ? AND realestate.category = ? AND realestate.price >= ? AND realestate.price <= ? AND realestate.bedrooms = ? AND realestate.visible = 1', array($forsale, $category, $minprice, $maxprice, $bedroom));
            }
        }
        
        
        public function countBrokersForPage() {
                return $this->db->fetchAssoc('SELECT COUNT(idbrokers) from brokers');
        }
        
        
        public function getBrokersForPage($begin, $end) {
            $begin = (int)$begin;
            $end = (int)$end;
            
            if(is_numeric($begin) == false || is_numeric($end) == false){
                return null;
            }
            
            return $this->db->fetchAll('SELECT 
                brokers.idbrokers, brokers.name, brokers.address, brokers.zip, brokers.city, brokers.email, brokers.phone, brokers.image
                FROM brokers
                LIMIT ' . $begin . ',' . $end . '');
        }
        
        public function getBrokerByRealeState($id) {
            return $this->db->fetchAssoc('SELECT 
                realestate.broker
                FROM realestate
                WHERE realestate.idrealestate = ?', array($id));
        }
            
}