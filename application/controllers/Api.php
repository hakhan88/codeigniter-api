<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('api_model');
		$this->load->library('form_validation');
	}

	function index() {
		$data = $this->api_model->fetch_all();
		echo json_encode($data->result_array());
	}

	function isWeekend($date) {
		return (date('N', strtotime($date)) >= 6);
	}

	function insert() {
		$this->form_validation->set_rules('first_name', 'First Name', 'required');
		$this->form_validation->set_rules('last_name', 'Last Name', 'required');
		$this->form_validation->set_rules('date_slot', 'Date', 'required');
		$this->form_validation->set_rules('time_slot', 'Time', 'required');
		if ($this->form_validation->run()) {
			$availability = $this->api_model->check_availability($this->input->post('date_slot'), $this->input->post('time_slot'));
			$numberOfBookingsPerHour = $this->isWeekend($this->input->post('date_slot')) ? 4 : 2;

			if (count($availability) < $numberOfBookingsPerHour) {
				$data = array(
					'first_name'	=>	$this->input->post('first_name'),
					'last_name'		=>	$this->input->post('last_name'),
					'date_slot'		=>	$this->input->post('date_slot'),
					'time_slot'		=>	$this->input->post('time_slot'),
				);
	
				$this->api_model->insert_api($data);
				$array = array(
					'success'		=>	true
				);
			} else {
				$array = array(
					'error'					=>	true,
					'time_slot_taken'		=>	'Time slot is taken, please choose another time',
				);
			}

		} else {
			$array = array(
				'error'					=>	true,
				'first_name_error'		=>	form_error('first_name'),
				'last_name_error'		=>	form_error('last_name')
			);
		}
		echo json_encode($array);
	}
	
	function fetch_single() {
		if ($this->input->post('id')) {
			$data = $this->api_model->fetch_single_user($this->input->post('id'));
			foreach($data as $row) {
				$output['first_name'] = $row['first_name'];
				$output['last_name'] = $row['last_name'];
				$output['date_slot'] = $row['date_slot'];
				$output['time_slot'] = $row['time_slot'];
			}
			echo json_encode($output);
		}
	}

	function update() {
		$this->form_validation->set_rules('first_name', 'First Name', 'required');
		$this->form_validation->set_rules('last_name', 'Last Name', 'required');

		if ($this->form_validation->run()) {
			$availability = $this->api_model->check_availability($this->input->post('date_slot'), $this->input->post('time_slot'));

			$numberOfBookingsPerHour = $this->isWeekend($this->input->post('date_slot')) ? 4 : 2;
			if (count($availability) < $numberOfBookingsPerHour) {
				$data = array(
					'first_name'		=>	$this->input->post('first_name'),
					'last_name'			=>	$this->input->post('last_name'),
					'date_slot'			=>	$this->input->post('date_slot'),
					'time_slot'			=>	$this->input->post('time_slot'),
				);
				$this->api_model->update_api($this->input->post('id'), $data);
				$array = array(
					'success'		=>	true
				);
			} else {
				$array = array(
					'error'					=>	true,
					'time_slot_taken'		=>	'Time slot is taken, please choose another time',
				);
			}
		} else {
			$array = array(
				'error'				=>	true,
				'first_name_error'	=>	form_error('first_name'),
				'last_name_error'	=>	form_error('last_name')
			);
		}
		echo json_encode($array);
	}

	function delete() {
		if ($this->input->post('id')) {
			if ($this->api_model->delete_single_user($this->input->post('id'))) {
				$array = array(
					'success'	=>	true
				);
			} else {
				$array = array(
					'error'		=>	true
				);
			}
			echo json_encode($array);
		}
	}
}

?>