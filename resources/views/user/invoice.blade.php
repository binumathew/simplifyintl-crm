<div class="row">
    <div class="col-md-12">
        <div class="card m-b-20">
            <div class="card-body invoice">
            <h4 class="mt-0 header-title">Invoice</h4>
            <hr>
            <form id="invoice_form">
            <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <select name="invoice_month" id="invoice_month" class="form-control custom-select required">
                                <option value="">Choose</option>
                                    @php
                                        $now       = Carbon::now();
                                        $month     = $now->startOfMonth()->subMonth();
                                        $currmonth = $now->startOfMonth()->format('m');
                                    @endphp
                                @for($i=1; $i<=12; $i++)
                                    @php 
                                        $month_name = $now->format('F');
                                        $monthid    = $now->format('m');
                                        $month      = $month->addMonth();
                                    @endphp
                                <option {{ ($currmonth == $monthid) ? 'selected':'' }} value="{{base64_encode($monthid)}}">{{$month_name}}</option>  
                                @endfor
                            </select>
                        </div>
                    </div> 
                    <div class="col-md-3">
                        <div class="form-group">
                            <select name="invoice_year" id="invoice_year" class="form-control custom-select required">
                                <option value="">Choose</option>
                                @php 
                                    $firstYear = (int)Carbon::now()->format('Y'); 
                                    $lastYear = $firstYear - 5;
                                @endphp
                                @for($i=$firstYear;$i >= $lastYear;$i--)
                                <option {{ ($i == $firstYear) ? 'selected':'' }} value="{{base64_encode($i)}}">{{$i}}</option>  
                                @endfor
                            </select>
                        </div>
                    </div>
                <!-- <div class="col-md-1">
                    <button type="button" class="btn btn-success waves-effect waves-light">Search</button>
                </div>   -->
                <div class="col-md-1">
                    <button type="button" class="btn btn-primary waves-effect waves-light" id="createinvoice">Generate</button>
                </div>  
            </div>
            </form>
            <!-- <div class="row">
            <div class="col-md-12">
            <table id="invoicetable" class="table table-striped dt-responsive" cellspacing="0">
                <thead>
                    <tr>
                        <th>SL No</th>
                        <th>Year</th>
                        <th>Month</th>
                        <th>Sub-Total</th>
                        <th>Vat/Tax</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
            </div>
            </div> -->
            </div>
        </div>
    </div>
</div>