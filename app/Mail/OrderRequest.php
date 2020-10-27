<?php

namespace App\Mail;

use Helper;
use App\Models\SimRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderRequest extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->data->order = SimRequest::whereId($this->data->request_id)->get();
        foreach ($this->data->order as $item) {
            foreach($item->list as $simList) {            
                $sim['plan_name'] = $simList->auto_plan->plan->plan_name;
                $sim['plan_price'] = $simList->auto_plan->amount;
                $sim['phone_number'] = ($simList->stock->verify)?$simList->stock->phone_number:'0759xxxxxxx';   
                $sim['sim_cost'] = $simList->stock->price;         
                $sim['extra_credit'] = $simList->credit;  
                $simDetails[$simList->autoplan_id][] =  $sim;           
            }
            $item->sim_data = $simDetails;
        }

        $currency = Helper::get_option('currency_symbol');  
        return $this->subject($this->data->subject)
                    ->view('emails.order_request')->with('currency', $currency);
    }
}
