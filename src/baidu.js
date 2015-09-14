//add
var casper = require('casper').create({
	pageSettings:{loadImages:false}
	,verbose: true
	, pageSettings: {
        loadImages:  false,       
        loadPlugins: true,        
        userAgent: 'Mozilla/20.0 (Windows NT 6.1; rv:17.0) Gecko/20100101 Firefox/17.0'
   }

});

var debug=casper.cli.get('debug');


var producturl='http://ops.baidu.com/partner/index.php?r=PartnerChannelAction&start_date={start}&end_date={end}&exchange_type=0&product_id={product_id}&channel_number={channel}';
producturl=producturl.replace('{product_id}', casper.cli.get("product_id"));
producturl=producturl.replace('{start}', casper.cli.get("start"));
producturl=producturl.replace('{end}', casper.cli.get("end"));
producturl=producturl.replace('{channel}', casper.cli.get("channel"));

if(debug){
	casper.echo("start with:" + casper.cli.get('username') +" "+ casper.cli.get('password'));
}

function capture(img, msg){
	casper.capture(img, {
        top: 0,
        left: 0,
        width: 1024,
        height: 600
    });
    casper.echo(msg);
}

casper.on('http.status.404', function(resource) {
	casper.test.fail("404 error");
});

casper.on('error', function(msg, trace){
	casper.echo("error: " + msg + "\r\n" + trace);
});

casper.on('page.error', function(msg, trace){
	casper.echo("page.error: "+ msg + "\r\n" + trace);
});

casper.start('https://passport.baidu.com/v2/?login&fr=old&u=http%3A%2F%2Fops.baidu.com%2Fpartner%2F', function() {
	if(debug){
		capture('baidu_01.png', 'login start')
	}	
    this.fill('#loginForm', {
        'userName':    casper.cli.get('username'),// '759725139@qq.com',
        'password':    casper.cli.get('password') // 'jerry19850817'       
    }, false);

    if(debug){
		capture('baidu_02.png', 'login fill done');
	}
}).then(function(){
	this.wait(5000, function(){
		this.click('#TANGRAM__3__submit');
	})
}).then(function() {
	this.wait(4000, function() {
    	if(debug){
			capture('baidu_03.png', 'login complete done');
		}
    });
	this.page.addCookie({
	    'name': 'pop_box_close_flag',
	    'value': 1
	});
}).thenOpen(producturl,function(){
	this.page.addCookie({
		'name': 'pop_box_close_flag',
	    'value': 1
	});
}).thenOpen(producturl, function(){
//	this.debugHTML();
	if(debug){
		capture('baidu_04.png', 'product get done');
	}
    var arr = casper.evaluate(function() {
    	var result=[];
    	if(document.querySelector('.table tbody') && document.querySelector('.table tbody').rows != null){
    		var tbody = document.querySelector('.table tbody').rows;
    		for (var i = 0; i<tbody.length; i++) {
    			result.push(tbody[i].innerText);
    		}
    	}
    	return result;
	});

	this.each(arr, function(self, str){
		var item = str.split("\n");
		if(item[1]!=undefined){
			this.echo(item[1] +',' + item[3]);
		}
	}).exit();

});

casper.run();