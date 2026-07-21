<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class RentVehicle_m extends MY_Model {
	
	function list_vehicle($account_id,$param)
	{
		if($param['sort'] == 5)
		{
			$this->db->where('name','distance_max_rentvehicle');
			
			$distance = $this->db->get('config')->row()->value;
			$location = null;
			if($account_id != null)
			{
				$this->db->where('account_id',$account_id);
				$location = $this->db->get('customers_location')->row();
			}
			
			if($location != null && $location->latitude != null && $location->longitude != null)
			{
				//$this->db->having('distance > 0');
				$this->db->select('(6371 * acos(cos(radians('.$location->latitude.')) * cos(radians(customers_location.latitude)) * cos(radians(customers_location.longitude) - radians('.$location->longitude.')) + sin(radians('.$location->latitude.')) * sin( radians(customers_location.latitude)))) as distance');
			}else
			{
				$this->db->select( ($distance + 1).' as distance');
				//$this->db->having('distance > 0');
			}
			
			$this->db->having('distance <= '.$distance);
				
		}
		
		if($param['sort'] != null)
		{
			switch($param['sort'])
			{
				case 0 : 
					$this->db->order_by('rent_vehicles_item.date_modified',"DESC");break;
				case 1 :
					$this->db->order_by('rent_vehicles_item.title',"ASC");break;
				case 2 :
					$this->db->order_by('rent_vehicles_item.title',"DESC");break;
				case 3 :
					$this->db->order_by('rent_vehicles_item.price',"DESC");break;
				case 4 :
					$this->db->order_by('rent_vehicles_item.price',"ASC");break;
				case 5 :
					$this->db->order_by('distance',"ASC");break;
				default :
					$this->db->order_by('rent_vehicles_item.date_modified',"DESC");break;
			}
		}
		
		$this->db->where('rent_vehicles_item.status',1);
		
		if($param['functional_type'] != null)
			$this->db->where('rent_vehicles_item.functional_type',$param['functional_type']);
		
		if($param['with_driver'] != null){
			if($param['with_driver'] == 1)
			{
				$this->db->where('rent_vehicles_item.with_driver',1);
				$this->db->where('partners_config.force_with_driver',0);
			}else
				$this->db->where('rent_vehicles_item.with_driver',0);
		}
		if($param['min_passenger'] != null)
			$this->db->where('rent_vehicles_item.max_passenger >=', (int)$param['min_passenger']);

		if($param['max_passenger'] != null)
			$this->db->where('rent_vehicles_item.max_passenger <=', (int)$param['max_passenger']);

		if($param['min_price'] != null)
			$this->db->where('price >=', (float)$param['min_price']);

		if($param['max_price'] != null)
			$this->db->where('price <=', (float)$param['max_price']);
		
		if($param['start_date'] != null || $param['end_date'] != null)
		{
			$this->db->group_start();
			$this->db->where('`transaction_rent_vehicle`.number_book',0);
			$this->db->or_where('`transaction_rent_vehicle`.number_book IS NULL');
			$this->db->group_end();
		}
		if($param['regencies'] != null)
		    $this->db->where('partners.regencies_id',$param['regencies']);
		
		if($param['vehicle_functional_type_selected'] != null)
		    $this->db->where_in('rent_vehicles_item.functional_type',$param['vehicle_functional_type_selected']);
		
		$this->db->limit($param['limit'],( ($param['page']-1) * $param['limit'] ));
		$this->db->group_by('rent_vehicles_item.id');
		
		$this->db->select('rent_vehicles_item.id, rent_vehicles_item.title, rent_vehicles_item.functional_type,
		vehicle_type.name as vehicle_type_name, 
		rent_vehicles_item.with_driver, rent_vehicles_item.max_passenger,color.name as color_name,color.value as color_value,rent_vehicles_item.price,rent_vehicles_item.price_with_driver_basic, rent_vehicles_item.price_with_driver_full, rent_vehicles_item_images.img as img,review.rating,review.total_review');
		
		$this->db->join('(
							SELECT item_id, MIN(id) as id, MIN(img) as img
							FROM rent_vehicles_item_images
							GROUP BY item_id
						) rent_vehicles_item_images','rent_vehicles_item_images.item_id = rent_vehicles_item.id','left');
		$this->db->join('color','color.id = rent_vehicles_item.color_id','left');
		$this->db->join('vehicle_type','vehicle_type.id = rent_vehicles_item.vehicle_type','left');
		$this->db->join('partners_config','partners_config.account_id = rent_vehicles_item.account_id','left');
		
		if($param['start_date'] != null || $param['end_date'] != null)
		{
			$start_date = $this->db->escape($param['start_date'].' 00:00:00');
			$end_date = $this->db->escape($param['end_date'].' 23:59:59');
			$this->db->join("(
								SELECT COUNT(id) as number_book, item_id
								FROM transaction_rent_vehicle
								WHERE status NOT IN (8,10,11,12)
								AND start_date <= $end_date AND end_date >= $start_date
								GROUP BY item_id
								) transaction_rent_vehicle",
							'transaction_rent_vehicle.item_id = rent_vehicles_item.id','left');
		}
		$this->db->join('(
							SELECT 
							transaction_rent_vehicle.item_id,COUNT(review_vehicle.id) as total_review,
							FORMAT(IFNULL(SUM(review_vehicle.rating) / COUNT(review_vehicle.id),0),1) as rating
							FROM
							transaction_rent_vehicle
							LEFT JOIN 
							review_vehicle
							ON review_vehicle.transaction_id = transaction_rent_vehicle.id
							GROUP BY transaction_rent_vehicle.item_id
						) review','review ON review.item_id = rent_vehicles_item.id','LEFT');
		$this->db->join('partners','partners.account_id = rent_vehicles_item.account_id','left');
		$this->db->join('customers_location','customers_location.account_id = partners.account_id','left');
		    
		return $this->db->get('rent_vehicles_item')->result();
	}
	
	function list_promote_vehicle($account_id,$param)
	{
		if($param['sort'] == 5)
		{
			$this->db->where('name','distance_max_rentvehicle');
			
			$distance = $this->db->get('config')->row()->value;
			$location = null;
			if($account_id != null)
			{
				$this->db->where('account_id',$account_id);
				$location = $this->db->get('customers_location')->row();
			}
			
			if($location != null && $location->latitude != null && $location->longitude != null)
			{
				//$this->db->having('distance > 0');
				$this->db->select('(6371 * acos(cos(radians('.$location->latitude.')) * cos(radians(customers_location.latitude)) * cos(radians(customers_location.longitude) - radians('.$location->longitude.')) + sin(radians('.$location->latitude.')) * sin( radians(customers_location.latitude)))) as distance');
			}else
			{
				$this->db->select( ($distance + 1).' as distance');
				//$this->db->having('distance > 0');
			}
			
			$this->db->having('distance <= '.$distance);
				
		}
		
		/**
		if($param['sort'] != null)
		{
			switch($param['sort'])
			{
				case 0 : 
					$this->db->order_by('rent_vehicles_item.date_modified',"DESC");break;
				case 1 :
					$this->db->order_by('rent_vehicles_item.title',"ASC");break;
				case 2 :
					$this->db->order_by('rent_vehicles_item.title',"DESC");break;
				case 3 :
					$this->db->order_by('rent_vehicles_item.price',"DESC");break;
				case 4 :
					$this->db->order_by('rent_vehicles_item.price',"ASC");break;
				case 5 :
					$this->db->order_by('distance',"ASC");break;
				default :
					$this->db->order_by('rent_vehicles_item.date_modified',"DESC");break;
			}
		}
		**/
		
		$this->db->order_by('promote_rent_vehicle.viewer',"ASC");
		
		$this->db->where('rent_vehicles_item.status',1);
		
		if($param['functional_type'] != null)
			$this->db->where('rent_vehicles_item.functional_type',$param['functional_type']);
		
		if($param['with_driver'] != null){
			if($param['with_driver'] == 1)
			{
				$this->db->where('rent_vehicles_item.with_driver',1);
				$this->db->where('partners_config.force_with_driver',0);
			}else
				$this->db->where('rent_vehicles_item.with_driver',0);
		}
		if($param['min_passenger'] != null)
			$this->db->where('rent_vehicles_item.max_passenger >=', (int)$param['min_passenger']);

		if($param['max_passenger'] != null)
			$this->db->where('rent_vehicles_item.max_passenger <=', (int)$param['max_passenger']);

		if($param['min_price'] != null)
			$this->db->where('price >=', (float)$param['min_price']);

		if($param['max_price'] != null)
			$this->db->where('price <=', (float)$param['max_price']);
		
		if($param['start_date'] != null || $param['end_date'] != null)
		{
			$this->db->group_start();
			$this->db->where('`transaction_rent_vehicle`.number_book',0);
			$this->db->or_where('`transaction_rent_vehicle`.number_book IS NULL');
			$this->db->group_end();
		}
		if($param['regencies'] != null)
		    $this->db->where('partners.regencies_id',$param['regencies']);
		
		if($param['vehicle_functional_type_selected'] != null)
		    $this->db->where_in('rent_vehicles_item.functional_type',$param['vehicle_functional_type_selected']);
		
		$this->db->group_start();
		$this->db->where('DATE(NOW()) >= promote_rent_vehicle.start_date');
		$this->db->where('DATE(NOW()) <= promote_rent_vehicle.end_date');
		$this->db->group_end();
		
		$this->db->group_start();
		$this->db->where('promote_rent_vehicle.status',0);
		$this->db->or_where('promote_rent_vehicle.status',1);
		$this->db->group_end();
		
		$this->db->limit($param['limit'],( ($param['page']-1) * $param['limit'] ));
		$this->db->group_by('rent_vehicles_item.id');
		
		$this->db->select('promote_rent_vehicle.id as promote_id, 1 as promote,rent_vehicles_item.id, rent_vehicles_item.title, rent_vehicles_item.functional_type,
		vehicle_type.name as vehicle_type_name, 
		rent_vehicles_item.with_driver, rent_vehicles_item.max_passenger,color.name as color_name,color.value as color_value,rent_vehicles_item.price,rent_vehicles_item.price_with_driver_basic, rent_vehicles_item.price_with_driver_full, rent_vehicles_item_images.img as img,review.rating,review.total_review');
		
		$this->db->join('rent_vehicles_item','rent_vehicles_item.id = promote_rent_vehicle.item_id','left');
		$this->db->join('(
							SELECT item_id, MIN(id) as id, MIN(img) as img
							FROM rent_vehicles_item_images
							GROUP BY item_id
						) rent_vehicles_item_images','rent_vehicles_item_images.item_id = rent_vehicles_item.id','left');
		$this->db->join('color','color.id = rent_vehicles_item.color_id','left');
		$this->db->join('vehicle_type','vehicle_type.id = rent_vehicles_item.vehicle_type','left');
		$this->db->join('partners_config','partners_config.account_id = rent_vehicles_item.account_id','left');
		
		if($param['start_date'] != null || $param['end_date'] != null)
		{
			$start_date = $this->db->escape($param['start_date'].' 00:00:00');
			$end_date = $this->db->escape($param['end_date'].' 23:59:59');
			$this->db->join("(
								SELECT COUNT(id) as number_book, item_id
								FROM transaction_rent_vehicle
								WHERE status NOT IN (8,10,11,12)
								AND start_date <= $end_date AND end_date >= $start_date
								GROUP BY item_id
								) transaction_rent_vehicle",
							'transaction_rent_vehicle.item_id = rent_vehicles_item.id','left');
		}
		$this->db->join('(
							SELECT 
							transaction_rent_vehicle.item_id,COUNT(review_vehicle.id) as total_review,
							FORMAT(IFNULL(SUM(review_vehicle.rating) / COUNT(review_vehicle.id),0),1) as rating
							FROM
							transaction_rent_vehicle
							LEFT JOIN 
							review_vehicle
							ON review_vehicle.transaction_id = transaction_rent_vehicle.id
							GROUP BY transaction_rent_vehicle.item_id
						) review','review ON review.item_id = rent_vehicles_item.id','LEFT');
		$this->db->join('partners','partners.account_id = rent_vehicles_item.account_id','left');
		$this->db->join('customers_location','customers_location.account_id = partners.account_id','left');
		
		$vehicles = $this->db->get('promote_rent_vehicle')->result();
		
		//update promote status have been in to match date range
		
		$this->db->where('status',0);
		$this->db->group_start();
		$this->db->where('DATE(NOW()) >= promote_rent_vehicle.start_date');
		$this->db->where('DATE(NOW()) <= promote_rent_vehicle.end_date');
		$this->db->group_end();
		$this->db->set('status',1);
		$this->db->update('promote_rent_vehicle');
		
		//update promote status have been in to expired date range
		
		$this->db->where('status',1);
		$this->db->group_start();
		$this->db->where('DATE(NOW()) > promote_rent_vehicle.end_date');
		$this->db->group_end();
		$this->db->set('status',2);
		
		$this->db->update('promote_rent_vehicle');
		
		//update viewer of showed promote vehicle
		$update_promote = array();
		
		foreach($vehicles as $val)
		{
			$update_promote[] = array(
				'id' => $val->promote_id,
				'viewer' => '(viewer+1)'
			);
		}
		 
		if(sizeof($update_promote) > 0)
		{
			$this->db->set_update_batch($update_promote,'id',FALSE);
			$this->db->update_batch('promote_rent_vehicle',null, 'id');
		}
		return $vehicles;
	}
	
	function list_vehicle_min_max_value()
	{
		$this->db->select('LEAST(MIN(price),MIN(price_with_driver_basic),MIN(price_with_driver_full)) as price_min, GREATEST(MAX(price),MAX(price_with_driver_basic),MAX(price_with_driver_full)) as price_max');
		return $this->db->get('rent_vehicles_item')->row();
	}
	
	function vehicle_detail($id)
	{
		$this->db->where('rent_vehicles_item.id',$id);
		$this->db->select('rent_vehicles_item.*,vehicle_type.name as vehicle_type_name, brand.name as brand_name,vehicle_model.name as vehicle_model_name, color.name as color_name,color.value as color_value,driven_type.name as driven_type_name, transmition_type.name as transmition_type_name,fuel.name as fuel_type_name, status.name as status_name');
		
		$this->db->select('vehicle_type.icon as vehicle_type_icon, brand.icon as brand_icon, driven_type.icon as driven_type_icon, transmition_type.icon as transmition_type_icon, fuel.icon as fuel_type_icon');
		$this->db->join('status','status.id = rent_vehicles_item.status','left');
		$this->db->join('fuel','fuel.id = rent_vehicles_item.fuel_type','left');
		$this->db->join('transmition_type','transmition_type.id = rent_vehicles_item.transmition_type','left');
		$this->db->join('driven_type','driven_type.id = rent_vehicles_item.driven_type','left');
		$this->db->join('color','color.id = rent_vehicles_item.color_id','left');
		$this->db->join('brand','brand.id = rent_vehicles_item.brand_id','left');
		$this->db->join('vehicle_type','vehicle_type.id = rent_vehicles_item.vehicle_type','left');
		$this->db->join('vehicle_model','vehicle_model.id = rent_vehicles_item.vehicle_model','left');
		return $this->db->get('rent_vehicles_item')->row();
	}
	
	function vehicle_booked($id)
	{
		
		$this->db->where('item_id',$id);
		$this->db->where('status != 8 AND status !=10 AND status !=11 AND status !=12');
		$this->db->where('DATE(start_date) >= "'.date('Y-m-d').'"');
		$this->db->select('DATE(start_date) as start_date,DATE(end_date) as end_date');
		return $this->db->get('transaction_rent_vehicle')->result();
	}
	
	function vehicle_review($id, $param)
	{
		$this->db->limit($param['limit'],( ($param['page']-1) * $param['limit'] ));
		
		$this->db->order_by('review_vehicle.date_modified','DESC');
		$this->db->where('transaction_rent_vehicle.item_id',$id);
		$this->db->join('transaction_rent_vehicle','transaction_rent_vehicle.id = review_vehicle.transaction_id','left');
		$this->db->join('accounts','accounts.id = review_vehicle.account_id','left');
		$this->db->join('customers','customers.account_id = review_vehicle.account_id','left');
		$this->db->select('review_vehicle.id, CONCAT(accounts.first_name," ",accounts.last_name) as name,customers.img_profile, review_vehicle.comment, review_vehicle.rating, review_vehicle.date_modified');
		return $this->db->get('review_vehicle')->result();
	}
	
	function vehicle_review_total($id)
	{
		$this->db->where('transaction_rent_vehicle.item_id',$id);
		$this->db->join('transaction_rent_vehicle','transaction_rent_vehicle.id = review_vehicle.transaction_id','left');
		$this->db->select('count(review_vehicle.id) as total');
		return $this->db->get('review_vehicle')->row()->total;
	}
	
	function vehicle_photos($id)
	{
		$this->db->where('item_id',$id);
		return $this->db->get('rent_vehicles_item_images')->result();
	}
	
	
	function post_checkout($param)
	{
		$this->db->insert('transaction_rent_vehicle',$param);
		
		return $this->db->insert_id();
	}
	
	function add_timeline_transaction($timeline)
	{
		$this->db->insert('timeline_transaction_rent_vehicle',$timeline);
	}
	
	function get_transaction_status_name($id)
	{
		$this->db->where('id',$id);
		return $this->db->get('transaction_rent_vehicle_status')->row();
	}
	
	function update_transaction($id,$data)
	{
		$this->db->where('id',$id);
		$this->db->update('transaction_rent_vehicle',$data);
	}
	function update_transaction_status($id,$status_id)
	{
		$this->db->where('id',$id);
		$this->db->set('status',$status_id);
		$this->db->update('transaction_rent_vehicle');
	}
	
	function vehicles_recomendation($account_id = null)
	{
		
		$this->db->where('name','distance_recomendation_rentvehicle');
		$distance = $this->db->get('config')->row()->value;
		$location = null;
		if($account_id != null)
		{
			$this->db->where('account_id',$account_id);
			$location = $this->db->get('customers_location')->row();
		}
		
		
			
		$this->db->limit(10,0);
		$this->db->group_by('rent_vehicles_item.id');
		
		$this->db->order_by('review.rating','DESC');
		$this->db->where('rent_vehicles_item.status',1);
		
		if($location != null && $location->latitude != null && $location->longitude != null)
		{
			$this->db->having('distance <= '.$distance);
			$this->db->select('(6371 * acos(cos(radians('.$location->latitude.')) * cos(radians(partners.latitude)) * cos(radians(partners.longitude) - radians('.$location->longitude.')) + sin(radians('.$location->latitude.')) * sin( radians(partners.latitude)))) as distance');
		}else
		{
			$this->db->select('0 as distance');
		}
		
		$this->db->join('partners','partners.account_id = rent_vehicles_item.account_id','left');
		$this->db->join('regencies','regencies.id = partners.regencies_id','left');
		$this->db->join('(
							SELECT item_id, MIN(id) as id, MIN(img) as img
							FROM rent_vehicles_item_images
							GROUP BY item_id
						) rent_vehicles_item_images','rent_vehicles_item_images.item_id = rent_vehicles_item.id','left');
		$this->db->join('vehicle_type','vehicle_type.id = rent_vehicles_item.vehicle_type','left');
		$this->db->join('(
							SELECT 
							transaction_rent_vehicle.item_id,COUNT(review_vehicle.id) as total_review,
							FORMAT(IFNULL(SUM(review_vehicle.rating) / COUNT(review_vehicle.id),0),1) as rating
							FROM
							transaction_rent_vehicle
							LEFT JOIN 
							review_vehicle
							ON review_vehicle.transaction_id = transaction_rent_vehicle.id
							GROUP BY transaction_rent_vehicle.item_id
						) review','review ON review.item_id = rent_vehicles_item.id','LEFT');
						
		$this->db->select('rent_vehicles_item.id,0 as promote, partners.regencies_id, regencies.name as regencies_name, rent_vehicles_item.title,rent_vehicles_item.price,rent_vehicles_item.price_with_driver_basic, rent_vehicles_item.price_with_driver_full,rent_vehicles_item.with_driver, rent_vehicles_item_images.img as img,review.rating,review.total_review');
		return $this->db->get('rent_vehicles_item')->result();
	}
	
	function promote_vehicles_recomendation($account_id = null)
	{
		
		//$this->db->where('name','distance_recomendation_rentvehicle');
		//$distance = $this->db->get('config')->row()->value;
		$location = null;
		if($account_id != null)
		{
			$this->db->where('account_id',$account_id);
			$location = $this->db->get('customers_location')->row();
		}
		
		
			
		$this->db->limit(10,0);
		$this->db->group_by('rent_vehicles_item.id');
		
		$this->db->order_by('distance','DESC');
		$this->db->where('rent_vehicles_item.status',1);
		
		$this->db->group_start();
		$this->db->where('DATE(NOW()) >= promote_rent_vehicle.start_date');
		$this->db->where('DATE(NOW()) <= promote_rent_vehicle.end_date');
		$this->db->group_end();
		
		$this->db->group_start();
		$this->db->where('promote_rent_vehicle.status',0);
		$this->db->or_where('promote_rent_vehicle.status',1);
		$this->db->group_end();
		
		if($location != null && $location->latitude != null && $location->longitude != null)
		{
			//$this->db->having('distance <= '.$distance);
			$this->db->select('(6371 * acos(cos(radians('.$location->latitude.')) * cos(radians(partners.latitude)) * cos(radians(partners.longitude) - radians('.$location->longitude.')) + sin(radians('.$location->latitude.')) * sin( radians(partners.latitude)))) as distance');
		}else
		{
			$this->db->select('0 as distance');
		}
		
		$this->db->join('rent_vehicles_item','rent_vehicles_item.id = promote_rent_vehicle.item_id','left');
		$this->db->join('partners','partners.account_id = rent_vehicles_item.account_id','left');
		$this->db->join('regencies','regencies.id = partners.regencies_id','left');
		$this->db->join('(
							SELECT item_id, MIN(id) as id, MIN(img) as img
							FROM rent_vehicles_item_images
							GROUP BY item_id
						) rent_vehicles_item_images','rent_vehicles_item_images.item_id = rent_vehicles_item.id','left');
		$this->db->join('vehicle_type','vehicle_type.id = rent_vehicles_item.vehicle_type','left');
		$this->db->join('(
							SELECT 
							transaction_rent_vehicle.item_id,COUNT(review_vehicle.id) as total_review,
							FORMAT(IFNULL(SUM(review_vehicle.rating) / COUNT(review_vehicle.id),0),1) as rating
							FROM
							transaction_rent_vehicle
							LEFT JOIN 
							review_vehicle
							ON review_vehicle.transaction_id = transaction_rent_vehicle.id
							GROUP BY transaction_rent_vehicle.item_id
						) review','review ON review.item_id = rent_vehicles_item.id','LEFT');
						
		$this->db->select('rent_vehicles_item.id,1 as promote, partners.regencies_id, regencies.name as regencies_name, rent_vehicles_item.title,rent_vehicles_item.price,rent_vehicles_item.price_with_driver_basic, rent_vehicles_item.price_with_driver_full,rent_vehicles_item.with_driver, rent_vehicles_item_images.img as img,review.rating,review.total_review');
		return $this->db->get('promote_rent_vehicle')->result();
	}
}