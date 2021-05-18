<?php

class ControllerExtensionPaymentEpay extends Controller
{
    public function index()
    {
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        if ($order_info) {
            $data['backLink'] = $this->url->link('checkout/success');
            $data['failureBackLink'] = $this->url->link('extension/payment/epay/failureBackLink');
            $data['postLink'] = $this->url->link('extension/payment/epay/postLink');
            $data['description'] = 'Оплата в интернет магазине';

            $data['accountId'] = $this->config->get('payment_epay_client_id');
            $data['client_secret'] = $this->config->get('payment_epay_client_secret');
            $data['terminal'] = $this->config->get('payment_epay_terminal');

            $data['invoiceID'] = "00000" . $order_info['order_id'];
            $data['amount'] = $order_info['total'];
            $data['telephone'] = $order_info['telephone'];
            $data['email'] = $order_info['email'];

            $post = array(
                'grant_type' => 'client_credentials',
                'scope' => 'payment',
                'client_id' => $data['accountId'],
                'client_secret' => $data['client_secret'],
                'invoiceID' => $data['invoiceID'],
                'amount' => $data['amount'],
                'currency' => 'KZT',
                'terminal' => $data['terminal'],
                'postLink' => $data['postLink'],
                'failurePostLink' => $data['failureBackLink'],
            );

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, "https://testoauth.homebank.kz/epay2/oauth2/token");
            curl_setopt($ch, CURLOPT_POST, 1);


            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $data['response'] = curl_exec($ch);

            curl_close($ch);

            return $this->load->view('extension/payment/epay', $data);
        }
    }

    public function confirm()
    {
        $json = array();

        if ($this->session->data['payment_method']['code'] == 'epay') {
            $this->load->model('checkout/order');

            $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_epay_order_status_id'));
//			$json['redirect'] = $this->url->link('checkout/success');
            $json['success'] = true;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function postLink()
    {
        $json = file_get_contents('php://input');
        $result = json_decode($json, TRUE);

        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder((int)$result['invoiceId']);

        if ($order_info) {
            $comment = $order_info['comment'] . "\r\n";
            $comment .= "Сумма: {$result['amount']}\r\n";
            $comment .= "Банк: {$result['issuer']}\r\n";
            $comment .= "CARD HOLDER: {$result['name']}\r\n";
            $comment .= "\r\n";

            $this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$this->config->get('payment_epay_order_status_id') . "', comment = '{$comment}' WHERE order_id = '" . $result['invoiceId'] . "'");

            // Статус оплачен
            $this->model_checkout_order->addOrderHistory($result['invoiceId'], $this->config->get('payment_epay_post_order_status_id'));
        }
    }

    public function failureBackLink()
    {

    }
}
