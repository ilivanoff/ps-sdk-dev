<h1 class="head">Публикатор мыслей</h1>

<!--<div>
     "js-fileapi-wrapper" -- required class 
    <div class="js-fileapi-wrapper upload-btn">
        <div class="upload-btn__txt">Choose files</div>
        <input id="choose" name="files" type="file" multiple />
    </div>
    <div id="images"> previews </div>
</div>-->

<div class="container">
    <input type="file" id="choose-file" accept="image/*" />

    <!-- Wrap the image or canvas element with a block element -->
    <div class="crop-holder">
        {*<img id="image" src="https://farm6.staticflickr.com/5548/11874722676_6450fcb8ba_b.jpg">*}
        {*<img id="image" src="http://cdn3.vox-cdn.com/uploads/chorus_asset/file/917470/iphone-6-travel-photo-review-mann-header.0.jpg">*}
        {*<img id="image" src="http://i.ebayimg.com/images/i/161875836128-0-1/s-l1000.jpg">*}
        {*<img id="image" src="http://newshour-tc.pbs.org/newshour/wp-content/uploads/2015/04/Jupiter.jpg">*}
        {*<img id="image" src="http://cs402630.vk.me/v402630276/839b/fDvUp-RylzM.jpg">*}
        {*<img id="image" src="http://www.glcambodia.com/wp-content/uploads/2014/06/%D0%B0%D0%BD%D0%B3%D0%BA%D0%BE%D1%80-%D0%B2%D0%B0%D1%82-%D0%B2%D0%B5%D1%87%D0%B5%D1%80%D0%BE%D0%BC.jpg">*}
    </div>

    <a class='crop-upload' href='#'>Загрузить</a>

    <div class="crop-preview">
    </div>
    <div class="crop-preview-table">
        <div class="crop-preview-small">
        </div>
    </div>

    <div class="clearall"></div>

    <div class="crop-text">
        <textarea></textarea>
    </div>
</div>