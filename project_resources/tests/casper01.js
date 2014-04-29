var casper = require('casper').create({
    verbose: true,
    logLevel: 'debug',
    pageSettings: {
        loadImages: true,
        loadPlugins: false,
        userAgent: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_5) AppleWebKit/537.4 (KHTML, like Gecko) Chrome/22.0.1229.94 Safari/537.4'
    },
    waitTimeout: 10000
});

casper.start('http://cdev.newpassport.com/users/login', function afterstart() {

    if (this.exists('input[name="username"]')) {
        this.echo('that exists');
    }

});

casper.then(function fillForm(){
    this.fillSelectors("form.form-signin", {
        'input[name="username"]' : 'cdavis',
        'input[name="password"]' : 'secret',
    }, true);
});

casper.thenOpen('http://cdev.newpassport.com/scheduler', function openScheduler(){
    this.echo('Waiting to give scheduler a chance to draw....');
    this.wait(3000, function() {
        this.capture('scheduler-screenshot-01.png');
    });
});

casper.run();
