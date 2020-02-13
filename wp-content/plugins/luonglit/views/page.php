<div class="container-fluid" id="litbox">
    <div class="row">
        <div class="col-12">
            <nav>
                <div class="nav nav-tabs nav-fill" id="nav-tab" role="tablist">
                    <a class="nav-item nav-link active" data-toggle="tab" href="#nav-single" role="tab" aria-controls="nav-single" aria-selected="true">Lọc URL</a>
                    <a class="nav-item nav-link" data-toggle="tab" href="#nav-multi" role="tab" aria-controls="nav-multi" aria-selected="false">Lọc theo keyword</a>
                </div>
            </nav>
            <div class="tab-content py-3 px-3 px-sm-0">
                <div class="tab-pane fade show active" id="nav-single" role="tabpanel">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-12 col-md-12">
                                <label>Nhập vào đường dẫn youtube</label>
                                <select name="" id="single-link" multiple class="form-control l-m-select2" style="width: 100%"></select>
                            </div>
                            <div class="col-12 col-md-12">
                                <label>Chọn danh mục</label>
                                <select name="" id="single-categories" class="form-control l-select2" multiple>
                                    <?php
                                        $categories = get_categories([
                                            'hide_empty' => 0
                                        ]);

                                        foreach($categories as $value) {
                                    ?>
                                            <option value="<?php echo $value->cat_ID ?>"><?php echo $value->name ?></option>
                                    <?php
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label>Chọn ngôn ngữ</label>
                                <select name="" id="single-languages" class="form-control l-select2">
                                    <?php
                                    foreach($languages as $language) {
                                    ?>
                                        <option value="<?php echo $language['languageCode'] ?>"><?php echo $language['languageName']['simpleText'] ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-success" id="get-sub-btn">Get sub by links</button>
                    </div>
                    <div class="form-group">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>Title</th>
                                <th>Code</th>
                                <th>Status</th>
                            </tr>
                            </thead>
                            <tbody id="single-result">

                            </tbody>
                        </table>
                    </div>
                    <div class="form-group">
                        <button id="add-single-post" class="btn btn-warning">Add single post</button>
                    </div>
                </div>
                <div class="tab-pane fade" id="nav-multi" role="tabpanel">
                    <div class="form-group">
                        <blockquote class="alert alert-warning">
                            - Các video bị gạch chéo là video không có sub
                        </blockquote>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-6 col-md-4">
                                <label>Nhập vào từ khóa</label>
                                <select id="multi-search" class="form-control l-m-select2" multiple style="width: 400px"></select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-6 col-md-4">
                                <label>Số lượng cần import</label>
                                <input type="text" class="form-control" value="5" id="number" placeholder="Nhập vào số lượng">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-6 col-md-4">
                                <label>Danh mục</label>
                                <select name="" id="multi-categories" class="form-control l-select2" multiple style="width: 400px">
                                    <?php
                                    foreach($categories as $value) {
                                        ?>
                                        <option value="<?php echo $value->cat_ID ?>"><?php echo $value->name ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-success" id="fetch-link-btn">Fetch link</button>
                    </div>
                    <div class="form-group" id="multi-result">

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>