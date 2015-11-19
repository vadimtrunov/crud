var workpiece = null;

var Item = function(data, workpieceUrl)
{
    var $elem;

    this.data = data;

    this.status = 1;

    function loadWorkpiece()
    {
        if(!workpiece)
        {
            $.ajax({
                url: workpieceUrl,
                async: false,
                cache: false,
                success: function(data){
                    workpiece = data;

                }
            });
        }
    }

    this.render = function()
    {
        $.each(data, function(key, val){
            var $control = $elem.find('[data-block-'+key+']');
            if($control){
                $control.each(function(id, block){
                    var attr = $(block).data('block-'+key);
                    if(attr){
                        if(attr == 'html')
                        {
                            $(block).html(val)
                        }
                        else{
                            $(block).attr(attr, val);
                        }
                    }
                })


            }
        });


        return $elem;
    };

    loadWorkpiece();
    $elem = $(workpiece);

    return this;
};

var AjaxLoader =function(pagination, items_block, standardCallback, workpieceUrl){

    $(window).load(function() {
        setTimeout(function() {
            window.onpopstate = function(){
                location.reload(true);
            };
        }, 0);
    });

    var page = items_block.data('page');

    var that = this;

    var items = [];
    var baseUrl = location.href;
    var filter = '';

    var pageLocation = location.href.split('?')[0];

    function checkPagination(count)
    {
        var pageSize = pagination.data('page-size');
        if(count <= pageSize){
            pagination.hide();
        } else {
            pagination.show().find('li.page-number').remove();
            var block = pagination.find('li.left-arrow');
            for(var pageNum = 1; pageNum <= Math.ceil(count/pageSize); pageNum++){
                var item = $('<li class="page-number"><a></a></li>');
                item.find('a').attr('data-page', pageNum).html(pageNum);
                if(page == pageNum)
                {
                    item.find('a').addClass('active');
                }
                block.after(item);
                block = item;
            }
        }
    }

    this.load = function(url, callback, noPageCallback)
    {
        $.get(url, {'sort': $('#estate-items-sort').hasClass('reverse') ? 'reverse':'forward', 'page':page},function(data){
            history.pushState({}, '', this.url);
            baseUrl = url;
            checkPagination(data.count);
            if(data.items.length > 0 && callback)
            {
                callback();
            }
            else if(data.items.length == 0 && noPageCallback)
            {
                noPageCallback();
            }
            for(var i = 0; i < data.items.length; i++){
                var item = new Item(data.items[i], workpieceUrl);
                items.push(item);
                items_block.append(standardCallback(item));
            }
            if(typeof showMarkers != 'undefined')
            {
                showMarkers(items_block.find('.item'));
            }

            if($('#estate-items-count').length){
                $('#estate-items-count').html(data.count)
            }
            totalItemsCount = data.count;
            var event = document.createEvent('Event');
            event.initEvent('ajaxLoadFinish', true, true);
            document.dispatchEvent(event);
        });
    };

    this.loadPageBtn = function(event){
        event.stopPropagation();
        event.preventDefault();
        page = items_block.data('page')+1;
        var self = $(this);
        that.load(self.attr('href'), function(){
            items_block.data('page', page);
        }, function(){
            self.remove();
        });
    };

    this.loadLink = function(event){
        event.stopPropagation();
        event.preventDefault();
        var url = $(this).attr('href');
        page = 1;
        items_block.html('');
        that.load(url, function(){
            items_block.html('');
            $('#load-page>a').attr('href', url);
            items_block.data('page', 1);
        });
    };

    this.loadFilter = function(event){
        event.stopPropagation();
        event.preventDefault();
        var url = $(this).attr('action')+'?'+$(this).serialize()+'&'+filter;
        items_block.html('');
        that.load($(this).attr('action')+'?'+$(this).serialize()+'&'+filter, function(){
            items_block.html('');
            $('#load-page>a').attr('href', url);
            items_block.data('page', 1);
            page = 1;
        });
    };

    this.loadStandardFilter = function(event)
    {
        event.preventDefault();
        event.stopPropagation();
        filter = $(this).data('filter');
        page = 1;
        items_block.data('page', 1);
        items_block.html('');
        if(filter)
        {
            filter = 'filter='+filter;
            var url = pageLocation+'?'+$('#advanced-filter-block>#simple-filter-form').serialize()+'&'+filter;
            that.load(url, function(){
                items_block.html('');
                $('#load-page>a').attr('href', url);

            });
        }
        else{
            filter = '';
            that.load(pageLocation+'?'+$('#advanced-filter-block>#simple-filter-form').serialize(), function(){
                items_block.html('');
                $('#load-page>a').attr('href', pageLocation+'?'+$('#advanced-filter-block>#simple-filter-form').serialize());
            });
        }
    };

    this.loadPagination = function(event)
    {
        event.preventDefault();
        event.stopPropagation();
        if(page != $(this).data('page')){
            items_block.html('');
            pagination.find('li.page-number>a').removeClass('active');
            $(this).addClass('active');
            page = $(this).data('page');
            that.load(baseUrl, function(){
                items_block.html('');
                items_block.data('page', $(this).data('page'));
            });
        }
    };

    this.loadPrevPage = function(event)
    {
        event.preventDefault();
        event.stopPropagation();
        if(page > pagination.find('li.page-number>a').first().data('page'))
        {
            page--;
            pagination.find('li.page-number>a').removeClass('active');
            $('#estate-items-standard-pagination>li.page-number>a[data-page='+page+']').addClass('active');
            items_block.data('page', page);
            items_block.html('');
            that.load(baseUrl, function(){
                items_block.html('');
                items_block.data('page', $(this).data('page'));
            });
        }
    };

    this.loadNextPage = function(event)
    {
        event.preventDefault();
        event.stopPropagation();
        if(page < pagination.find('li.page-number>a').last().data('page'))
        {
            page++;
            pagination.find('li.page-number>a').removeClass('active');
            $('#estate-items-standard-pagination>li.page-number>a[data-page='+page+']').addClass('active');
            items_block.data('page', page);
            items_block.html('');
            that.load(baseUrl, function(){
                items_block.html('');
                items_block.data('page', $(this).data('page'));
            });
        }
    };

    this.loadSort = function(event)
    {
        event.preventDefault();
        event.stopPropagation();
        if($(this).hasClass('reverse')){
            $(this).removeClass('reverse');
        }else{
            $(this).addClass('reverse');
        }
        items_block.html('');
        that.load(baseUrl, function(){
            items_block.html('');
            items_block.data('page', 1);
            page = 1;
        });
    };


    $(function(){
        pagination = $('#estate-items-standard-pagination');
    });

};