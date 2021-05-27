@extends('shared.base')
@section('title')
    Home
@endsection

@section('menus')
    Bla Bla Bla Bla
@endsection

@section('content')
    <form method="post" action="{{url('/test')}}" id="form1">
        <div class="clearfix"></div>
        {{--Start Title Page--}}
        <div class="row">
            <div class="col-md-6 col-sm-12 col-xs-12">
                <h4>Listing Page</h4>
            </div>
            <div class="col-md-6 col-sm-12 col-xs-12">
                <div class="btn-group pull-right px-2">
                    <button type="button" class="btn btn-danger btn-sm">Actions</button>
                    <button type="button" class="btn btn-danger btn-sm dropdown-toggle" data-toggle="dropdown"
                            aria-expanded="false">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                        <li>
                            <button class="btn btn-primary pull-right btn-sm" type="button">Button 7</button>
                        </li>
                        <li><a href="javascript:;" onclick="App.submitForm('form1')">Button 8</a></li>
                        <li><a href="javascript:;">Button 9</a></li>
                    </ul>
                </div>
                <button class="btn btn-primary pull-right btn-sm" type="button">Button 1</button>
                <button class="btn btn-success pull-right btn-sm" type="button">Button 2</button>
                <button class="btn btn-danger pull-right btn-sm" type="button">Button 3</button>
                <button class="btn btn-warning pull-right btn-sm" type="button">Button 4</button>
                <button class="btn btn-info pull-right btn-sm" type="button">Button 5</button>
                <button class="btn btn-default pull-right btn-sm" type="button">Button 6</button>
            </div>
        </div>
        <hr class="col-12 title-divider">
        {{--End Title Page--}}
        <div class="clearfix"></div>
        {{--Start Search Form--}}
        <div class="row">
            <div class="form-group col-sm-6 col-md-4 col-lg-3 col-xs-12 bad">
                <label class="control-label" for="ex3">
                    Email address 3 <span class="require-flag">*</span></label>
                <input type="email" id="ex3" class="form-control input-sm" placeholder=" ">
                <span class="input-alert">More example invalid feedback text</span>

            </div>
            <div class="col-12">
                <div class="form-group col-sm-6 col-md-4 col-lg-3 col-xs-12 ">
                    <label class="control-label" for="ex2">Email address 2</label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="ex2" class="form-control" placeholder=" ">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-default"><i class="fa fa-calendar"></i></button>
                        </div>
                    </div>
                </div>
                <div class="form-group col-sm-6 col-md-4 col-lg-3 col-xs-12">
                    <label class="control-label" for="ex7">Email address 7</label>
                    <select name="test" id="ex7" class="form-control input-sm">
                        <option value="">Option 1</option>
                        <option value="">Option 2</option>
                    </select>
                </div>
                <div class="form-group col-sm-6 col-md-4 col-lg-3 col-xs-12">
                    <label class="control-label" for="ex1">Email address 1</label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="ex1" class="form-control input-sm" placeholder=" ">
                        <div class="input-group-btn">
                            <button class="btn btn-default"><i class="fa fa-adjust"></i></button>
                            {{--<button class="btn btn-primary"><i class="fa fa-adjust"></i></button>--}}
                            {{--<button class="btn btn-primary"><i class="fa fa-adjust"></i></button>--}}
                        </div>
                    </div>
                </div>
                <div class="form-check col-sm-6 col-md-4 col-lg-3 col-xs-12 ">
                    <label class="control-label" for="ex4">Radio Button</label>
                    <div class="form-check-input" id="ex4">
                        <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios1" value="option1">
                        <label class="form-check-label" for="exampleRadios1">
                            Yes
                        </label>
                        <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios2" value="option2">
                        <label class="form-check-label" for="exampleRadios2">
                            No
                        </label>
                        <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios3" value="option3">
                        <label class="form-check-label" for="exampleRadios3">
                            Test
                        </label>
                    </div>
                    {{--<span class="input-alert">More example invalid feedback text</span>--}}
                </div>
                <div class="form-group col-sm-6 col-md-4 col-lg-3 col-xs-12">
                    <label class="control-label" for="ex5">Email address 5</label>
                    <input type="email" id="ex5" class="form-control input-sm" placeholder=" ">
                </div>
                <div class="form-group col-sm-6 col-md-4 col-lg-3 col-xs-12">
                    <label class="control-label" for="ex6">Email address 6</label>
                    <input type="email" id="ex6" class="form-control input-sm" placeholder=" ">
                </div>
            </div>
        </div>
        {{--End Search Form--}}
        <div class="clearfix"></div>
        <div class="row">
            <div class="col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <p class="title pull-left">Results</p>
                        <div class="portlet-paging">
                            <p class="pagination-inner pull-right">Showing 10 data of 158</p>
                            <div class="paging-simple-number pull-right">
                                <ul class="pagination ">
                                    <li class="disabled"><span>First</span></li>
                                    <li class=""><a href="#"><</a></li>
                                    <li class="active"><a href="#">1</a></li>
                                    <li class=""><a href="#">2</a></li>
                                    <li class="paginate_button"><a href="#">3</a></li>
                                    <li class="paginate_button"><a href="#">4</a></li>
                                    <li class="paginate_button"><a href="#">4</a></li>
                                    <li class="paginate_button"><a href="#">></a></li>
                                    <li class="paginate_button"><a href="#">Last</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <table class="table table-bordered jambo_table">
                            <thead>
                            <tr class="headings">
                                <th>#</th>
                                <th>Job Number</th>
                                <th>Customer</th>
                                <th>Customer Ref</th>
                                <th>Vendor</th>
                                <th>Truck No</th>
                                <th>Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <th scope="row">1</th>
                                <td>TR-0001</td>
                                <td>Customer 1</td>
                                <td>CM-001</td>
                                <td>Suplier 01</td>
                                <td>B 1278 DE</td>
                                <td>Delivered</td>
                            </tr>
                            <tr>
                                <th scope="row">2</th>
                                <td>TR-0001</td>
                                <td>Customer 1</td>
                                <td>CM-001</td>
                                <td>Suplier 01</td>
                                <td>B 1278 DE</td>
                                <td>Delivered</td>
                            </tr>
                            <tr>
                                <th scope="row">3</th>
                                <td>TR-0001</td>
                                <td>Customer 1</td>
                                <td>CM-001</td>
                                <td>Suplier 01</td>
                                <td>B 1278 DE</td>
                                <td>Delivered</td>
                            </tr>
                            <tr>
                                <th scope="row">4</th>
                                <td>TR-0001</td>
                                <td>Customer 1</td>
                                <td>CM-001</td>
                                <td>Suplier 01</td>
                                <td>B 1278 DE</td>
                                <td>Delivered</td>
                            </tr>
                            <tr>
                                <th scope="row">5</th>
                                <td>TR-0001</td>
                                <td>Customer 1</td>
                                <td>CM-001</td>
                                <td>Suplier 01</td>
                                <td>B 1278 DE</td>
                                <td>Delivered</td>
                            </tr>
                            <tr>
                                <th scope="row">6</th>
                                <td>TR-0001</td>
                                <td>Customer 1</td>
                                <td>CM-001</td>
                                <td>Suplier 01</td>
                                <td>B 1278 DE</td>
                                <td>Delivered</td>
                            </tr>
                            <tr>
                                <th scope="row">7</th>
                                <td>TR-0001</td>
                                <td>Customer 1</td>
                                <td>CM-001</td>
                                <td>Suplier 01</td>
                                <td>B 1278 DE</td>
                                <td>Delivered</td>
                            </tr>
                            <tr>
                                <th scope="row">8</th>
                                <td>TR-0001</td>
                                <td>Customer 1</td>
                                <td>CM-001</td>
                                <td>Suplier 01</td>
                                <td>B 1278 DE</td>
                                <td>Delivered</td>
                            </tr>
                            <tr>
                                <th scope="row">9</th>
                                <td>TR-0001</td>
                                <td>Customer 1</td>
                                <td>CM-001</td>
                                <td>Suplier 01</td>
                                <td>B 1278 DE</td>
                                <td>Delivered</td>
                            </tr>
                            <tr>
                                <th scope="row">10</th>
                                <td>TR-0001</td>
                                <td>Customer 1</td>
                                <td>CM-001</td>
                                <td>Suplier 01</td>
                                <td>B 1278 DE</td>
                                <td>Delivered</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        {{--End Listing Table--}}
    </form>
@endsection
