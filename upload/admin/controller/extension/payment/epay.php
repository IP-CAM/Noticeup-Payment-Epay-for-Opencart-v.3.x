<?php
class ControllerExtensionPaymentEpay extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/payment/epay');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('payment_epay', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/epay', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/payment/epay', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

		if (isset($this->request->post['payment_epay_client_id'])) {
			$data['payment_epay_client_id'] = $this->request->post['payment_epay_client_id'];
		} else {
			$data['payment_epay_client_id'] = $this->config->get('payment_epay_client_id');
		}

        if (isset($this->request->post['payment_epay_client_secret'])) {
            $data['payment_epay_client_secret'] = $this->request->post['payment_epay_client_secret'];
        } else {
            $data['payment_epay_client_secret'] = $this->config->get('payment_epay_client_secret');
        }

        if (isset($this->request->post['payment_epay_terminal'])) {
            $data['payment_epay_terminal'] = $this->request->post['payment_epay_terminal'];
        } else {
            $data['payment_epay_terminal'] = $this->config->get('payment_epay_terminal');
        }

        if (isset($this->request->post['payment_epay_total'])) {
            $data['payment_epay_total'] = $this->request->post['payment_epay_total'];
        } else {
            $data['payment_epay_total'] = $this->config->get('payment_epay_total');
        }

		if (isset($this->request->post['payment_epay_order_status_id'])) {
			$data['payment_epay_order_status_id'] = $this->request->post['payment_epay_order_status_id'];
		} else {
			$data['payment_epay_order_status_id'] = $this->config->get('payment_epay_order_status_id');
		}

        if (isset($this->request->post['payment_epay_post_order_status_id'])) {
            $data['payment_epay_post_order_status_id'] = $this->request->post['payment_epay_post_order_status_id'];
        } else {
            $data['payment_epay_post_order_status_id'] = $this->config->get('payment_epay_post_order_status_id');
        }

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['payment_epay_geo_zone_id'])) {
			$data['payment_epay_geo_zone_id'] = $this->request->post['payment_epay_geo_zone_id'];
		} else {
			$data['payment_epay_geo_zone_id'] = $this->config->get('payment_epay_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        if (isset($this->request->post['payment_epay_mode'])) {
            $data['payment_epay_mode'] = $this->request->post['payment_epay_mode'];
        } else {
            $data['payment_epay_mode'] = $this->config->get('payment_epay_mode');
        }

		if (isset($this->request->post['payment_epay_status'])) {
			$data['payment_epay_status'] = $this->request->post['payment_epay_status'];
		} else {
			$data['payment_epay_status'] = $this->config->get('payment_epay_status');
		}

		if (isset($this->request->post['payment_epay_sort_order'])) {
			$data['payment_epay_sort_order'] = $this->request->post['payment_epay_sort_order'];
		} else {
			$data['payment_epay_sort_order'] = $this->config->get('payment_epay_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/epay', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/epay')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}