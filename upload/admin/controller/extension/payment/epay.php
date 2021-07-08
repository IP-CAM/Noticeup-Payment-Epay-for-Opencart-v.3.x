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

        if (isset($this->request->post['payment_epay_transaction_order_status_id'])) {
            $data['payment_epay_transaction_order_status_id'] = $this->request->post['payment_epay_transaction_order_status_id'];
        } else {
            $data['payment_epay_transaction_order_status_id'] = $this->config->get('payment_epay_transaction_order_status_id');
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

	public function token()
    {
        $order_id = $this->request->get['order_id'];

        $this->load->model('sale/order');

        $order_info = $this->model_sale_order->getOrder($this->request->get['order_id']);

        if ($this->config->get('payment_epay_post_order_status_id') == $order_info['order_status_id']) {
            $post = array(
                'grant_type' => 'client_credentials',
                'scope' => 'webapi usermanagement statement',
                'client_id' => $this->config->get('payment_epay_client_id'),
                'client_secret' => $this->config->get('payment_epay_client_secret'),
            );

            $ch = curl_init();

            // Получаем токен
            curl_setopt($ch, CURLOPT_URL,"https://epay-oauth.homebank.kz/oauth2/token");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $json['token'] = json_decode(curl_exec($ch), TRUE)['access_token'];
            curl_close ($ch);

            $ch = curl_init();
            // Получаем транзакцию
            curl_setopt($ch, CURLOPT_URL,"https://epay-api.homebank.kz/operation/" . $order_id);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', "Authorization: Bearer " . $json['token']));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

            $json['id'] = json_decode(curl_exec($ch), TRUE)['id'];
            curl_close ($ch);

            $json['success'] = true;
        } else {
            $json['error'] = true;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function transaction() {
        $this->load->language('sale/order');

        $json = array();

        if (!$this->user->hasPermission('modify', 'sale/order')) {
            $json['error'] = $this->language->get('error_permission');
        } elseif (isset($this->request->get['order_id'])) {

            if (isset($this->request->get['id'])) {
                $epay_id = $this->request->get['id'];
            }

            if (isset($this->request->get['token'])) {
                $epay_token = $this->request->get['token'];
            }

            $ch = curl_init();
            // Получаем транзакцию
            curl_setopt($ch, CURLOPT_URL,"https://epay-api.homebank.kz/operation/" . $epay_id . "/charge");
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', "Authorization: Bearer ". $epay_token));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $result = json_decode(curl_exec($ch), TRUE);
            curl_close ($ch);

            if ($result['code'] == 0) {
                $json['transaction_status_id'] = $this->config->get('payment_epay_transaction_order_status_id');

                $json['success'] = $result['message'];
            } else {
                $json['error'] = $result['message'];
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/epay')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}