<?php 
	class Objects extends CI_Controller {

		public function view($id) {

			$valid_id = $this->security->xss_clean($id);

			$found_object = $this->object_model->get_object_by_id($valid_id);

			if(!$this->object_model->update_popularity_counter($id)){

				die("Eror while updating a pop_counter in database");

			} // increase a popularity counter by 1 point up;

			if(empty($found_object)) {

				$message = 'No results found...';

				$object_type_single = null;

				$phones = null;

				$images = null;

				$object_type = null;

				
			} else {

				$object_type_single = $this->object_model->get_object_type_by_inst_id($found_object->obj_inst_id);

				$phones = $this->object_model->get_object_phones_by_obj_id($found_object->obj_id);

				$images = $this->object_model->get_object_images($valid_id);

				$object_type = $object_type_single->name_single;

				$message = null;
				
			}

			$data = [
					'object' => $found_object,
					'object_type' => $object_type,
					'phones' => $phones,
					'images' => $images,
					'message' => $message
				];

			
			// var_dump($found_object->status);


			# IF: there is some Results

			if(!empty($found_object)){
				
				if ($found_object->status == 0 && $this->user_model->isAdmin()) {

					$this->load->view('templates/header_unchecked');
					$this->load->view('pages/single_object', $data);
					$this->load->view('templates/footer');

				} else if($found_object->status == 0 && !$this->user_model->isAdmin()){

					$data['message'] = 'This object is still being <br>processed...';

					$this->load->view('templates/header');
					$this->load->view('pages/message_page', $data);
					$this->load->view('templates/footer');

				} else if($found_object->status == 1){

					$this->load->view('templates/header');
					$this->load->view('pages/single_object', $data);
					$this->load->view('templates/footer');

				}

			}

			# IF: there is no results

			  else if(!empty($message) && empty($found_object)){
				
				$this->load->view('templates/header');
				$this->load->view('pages/message_page', $data);
				$this->load->view('templates/footer');

			} else {
				redirect('/home_users');
			}

			
			

		}


		public function create_object(){

				$country_id = $this->security->xss_clean($this->input->post('country_id'));

				$city_id = $this->security->xss_clean($this->input->post('city_id'));

				$inst_id = $this->security->xss_clean($this->input->post('inst_id'));

				$name = $this->security->xss_clean($this->input->post('name'));

				$discount = $this->security->xss_clean($this->input->post('discount'));

				$phone_number = $this->security->xss_clean($this->input->post('phone_number'));

				$adress = $this->security->xss_clean($this->input->post('adress'));

				$website = $this->security->xss_clean($this->input->post('website'));

				$short_describtion = $this->security->xss_clean($this->input->post('short_describtion'));

				$full_describtion = $this->security->xss_clean($this->input->post('full_describtion'));	

				$owner_name = $this->security->xss_clean($this->input->post('owner_name'));

			if (!$this->input->is_ajax_request()) {

				$owner_email = $this->security->xss_clean($this->input->post('owner_email'));

				$owner_password = $this->security->xss_clean($this->input->post('owner_password'));

			}

			// $country_name = $this->search_model->get_country_name_by_id($country_id);

			// $city_name = $this->search_model->get_city_name_by_id($id);

			// $inst_name = $this->search_model->get_inst_name_by_id($id);

			// if (!empty($name) && !empty($discount) && !empty($owner_name) && !empty($phone_number) && !empty($adress) && !empty($website) && !empty($short_describtion) && !empty($full_describtion)) {

			# IF GET(USER_ID) == SESSION[USER_ID] { DOWNLOAD #1 TEMPLATE }
			# ELSE IF USER_ID !== SESSION[USER_ID] { SHOW ERROR AND REDIRECT TO HOME }
			# ELSE { SHOW THE USUAL FORM }

				

				if (!empty($country_id) && !empty($city_id) && !empty($inst_id)) {
					$has_data = true;
				} else {
					$has_data = false;
				}

				$data = [
            			'country_id' => $country_id,
            			'city_id' => $city_id,
            			'inst_id' => $inst_id,
            			'name' => $name,
            			'discount' => $discount,
            			'owner_name' => $owner_name,
            			'phone_number' => $phone_number,
            			'website' => $website,
            			'adress' => $adress,
            			'short_describtion' => $short_describtion,
            			'full_describtion' => $full_describtion,
            			'has_data' => $has_data
            		];
            	if(!$this->input->is_ajax_request()){
            		$data['owner_email'] = $owner_email;
            		$data['owner_password']	= $this->user_model->hashPassword($owner_password);
            		$data['is_ajax_request'] = false;
            	} 

            	if($this->input->is_ajax_request()){
            		$data['is_ajax_request'] = true;
            	}
				


            // --------___------___-----____--- AJAX  --------___------___-----____--- //
            // --------___------___-----____--- AJAX  --------___------___-----____--- //
           	// --------___------___-----____--- AJAX  --------___------___-----____--- //
            // --------___------___-----____--- AJAX  --------___------___-----____--- //


            if($this->input->is_ajax_request()){

            	$errors = array();

				foreach ($data as $input => $val) {

					if ($val == "" || empty($val)) {

						$error_text = 'Fill out '. $input .' input first';
						
						array_push($errors, $error_text); 

					} 

				} 

				$response = [

					'errors' => $errors

				];

				// var_dump($errors);

				if (!empty($errors[0])) {

					$this->load->view('ajax/ajax_create_response', $response);

				} else {

					$created_object = $this->object_model->create_object($data);

	        		if ($created_object['is_created']) {

	        			$this->add_photos($created_object['obj_id'], true);

	        		} else {

	        			die('Some error uccured...');

	        		}

				}

				
				// --------___------___-----____--- ENDING --------___------___-----____--- //


			} else {

				$this->form_validation->set_rules('name', 'Name', 'required|validate_name');

			$this->form_validation->set_rules('discount', 'Discount', 'required');


			$this->form_validation->set_rules('adress', 'Adress', 'required');
			$this->form_validation->set_rules('website', 'Website url', 'required');
			$this->form_validation->set_rules('short_describtion', 'Short describtion', 'required');
			$this->form_validation->set_rules('full_describtion', 'Full describtion', 'required');
			$this->form_validation->set_rules('owner_name', 'Owner Name', 'required');
			$this->form_validation->set_rules('owner_email', 'Name', 'required');
			$this->form_validation->set_rules('phone_number', 'Phone number', 'required');


			if ($this->form_validation->run() == FALSE)	{

                    $this->load->view('templates/header');
                    $this->load->view('pages/create_object', $data);
                    $this->load->view('templates/footer');
                    $this->load->view('templates/additional-footer-partners');

            }
            	else
            {		

        		$created_object = $this->object_model->create_object($data);

        		if ($created_object['is_created']) {

        			$userdata = [

        				'user_id' => $created_object['obj_user_id'],
        				'username' => $created_object['username']

        			];

        			$this->user_model->loginUser($userdata);

        			if ($this->session->user_id) {
        					
        				redirect('objects/add_photos/'. $created_object['obj_id']);

        			} else {

    					show_404('Some Error Occured....');

        			}

        			

        		} else {

        			die('Some error uccured...');

        		}

	                
                    

            }


			}


			

			// -----------------
			// Validation Errors
			// -----------------

            

			






// 
		}

		public function submit_object() {

			$obj_id = $this->input->post('obj_id');

			echo $this->object_model->submit_object($obj_id);
		}

		public function delete_object() {

			$obj_id = $this->security->xss_clean($this->input->post('obj_id'));

			if ($this->user_model->isAdmin()) {

				$result = $this->object_model->delete_object($obj_id);
				
			} else {

				if($obj_id && $this->object_model->get_object_by_id($obj_id) && $this->user_model->isAdminOfAnObject($obj_id, $this->session->user_id)) {

					$result = $this->object_model->delete_object($obj_id);


				} else {

					show_404();

				}

			}

			#middleware
			



			
		}



		public function add_photos($obj_id = null, $is_ajax_successful = false) {


			$object_id = $this->security->xss_clean($obj_id);

			if($object_id && $this->object_model->get_object_by_id($object_id) && $this->user_model->isAdminOfAnObject($object_id, $this->session->user_id)){

				$data = [
					'error' => null,
					'obj_title' => $this->object_model->get_object_by_id($object_id)->obj_title,
					'obj_id' => $object_id,
					'is_ajax_successful' => $is_ajax_successful
				];
				if ($is_ajax_successful) {
					$this->load->view('pages/add_photos', $data);
				} else {
					$this->load->view('templates/header');
					$this->load->view('pages/add_photos', $data);
					$this->load->view('templates/footer');
				}


	            

			} else {

				show_404();

			}
			
			

		}

		public function do_upload(){

				$config['upload_path']          = 'assets/uploaded_images';
	            $config['allowed_types']        = 'gif|jpg|png';
	            $config['max_size']             = 2048;
	            $config['max_width']            = 2048;
	            $config['max_height']           = 2048;


	        	$this->upload->initialize($config);

	        	// echo "<pre>";
	        	// print_r ($_FILES);
	        	// echo "</pre>";

               	$number_of_files = count($_FILES['userfile']['name']);
               	$files = $_FILES;

               	for($i = 0; $i < $number_of_files; $i++){


	               		$_FILES['userfile']['name'] = $files['userfile']['name'][$i];
	               		$_FILES['userfile']['type'] = $files['userfile']['type'][$i];
	               		$_FILES['userfile']['tmp_name'] = $files['userfile']['tmp_name'][$i];
	               		$_FILES['userfile']['error'] = $files['userfile']['error'][$i];
	               		$_FILES['userfile']['size'] = $files['userfile']['size'][$i];
	               		
	               		
	                	$error = null;
	               		if ( ! $this->upload->do_upload('userfile')){
	               			$error = array('error' => $this->upload->display_errors());
	               		}
	               		else {
	               			$data = array('upload_data' => $this->upload->data());
	               			// echo "<br> success";
	               			$this->object_model->add_images_to_object($this->input->post('obj_id'), $_FILES['userfile']['name']);
	               		}
               			


               	}

               	$data = [

       				'object_name' => $this->object_model->get_object_name_by_id($this->input->post('obj_id'))

       			];
	               			
       			$this->load->view('templates/header');
				$this->load->view('pages/object_creation', $data);
				$this->load->view('templates/footer');


                

				
				    
		} 



		public function get_discount() {

		 	$email = $this->security->xss_clean($this->input->post('email'));
		 	$name = $this->security->xss_clean($this->input->post('name'));
		 	$object_name = $this->object_model->get_object_by_id($this->security->xss_clean($this->input->post('object_id')))->obj_title;
		 	$object_owner_email = $this->object_model->get_object_by_id($this->security->xss_clean($this->input->post('object_id')))->obj_owner_email;

		 	$generated_key = $this->generate_key();

		 	

		 	// Setting the config
		 	$this->mailSetUp();
				

			

			// Sending an email to the user
		 	
		 	$this->email->from('test@hotel-shato.od.ua', 'M.I.R.');
		 	$this->email->to($email);
		 	
		 	$this->email->subject('MIR - Discount at'. $object_name);
		 	$this->email->message('Hi, '. $name .', your discount code: '. $generated_key. ' You can show this code to the owner of the object or place you chose, and you should be given an appropriate discount as shown on our website.'. ' Thanks for being with us!');
		 	
		 	$this->email->send();


		 	// Setting the config
		 	$this->mailSetUp();

		 	// Sending an email to the Owner
		 	$this->email->from('test@hotel-shato.od.ua', 'M.I.R.');
		 	$this->email->to($object_owner_email);
		 	
		 	$this->email->subject('MIR - Discount at'. $object_name);
		 	$this->email->message('Hi, owner of the '. $object_name .', you have a customer with this code: '. $generated_key. ' this is the code a customer received after visiting your object, be ready to meet this customer soon.'. ' Thanks for being with us!');
		 	
		 	$this->email->send();


		 redirect('/get_discount_success_page');
		 	
		 // NOTE: ADD a function that sends a mail to the owner of an object to
		 // inform them about coming customer and their keycode	


		 // echo $this->email->print_debugger();


		 }

		 public function generate_key($len = 5) {

		    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
		    $pass = array(); //remember to declare $pass as an array
		    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache

		    for ($i = 0; $i < $len; $i++) {
		        $n = rand(0, $alphaLength);
		        $pass[] = $alphabet[$n];
		    }

		    // echo implode($pass);


		    return implode($pass); //turn the array into a string
		}

		public function mailSetUp($smtp_settings = null) {

			$this->load->library('email');


			$config['protocol'] = 'smtp';
			$config['charset'] = 'utf-8';
			$config['wordwrap'] = TRUE;
			$config['smtp_host'] = "mx1.mirohost.net";

			if(!empty($smtp_settings['smtp_user']) && !empty($smtp_settings['smtp_pass'])) {

				$config['smtp_user'] = $smtp_settings['smtp_user'];
				$config['smtp_pass'] = $smtp_settings['smtp_pass'];

			} else {

				$config['smtp_user'] = "test@hotel-shato.od.ua";
				$config['smtp_pass'] = "27wC786hm69l";

			}

			$config['smtp_crypto'] = "ssl";
			$config['smtp_port'] = "465";

			$this->email->initialize($config);
				
			
		
			
			

		}



		



		

	}



	