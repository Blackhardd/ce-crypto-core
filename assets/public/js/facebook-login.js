jQuery(document).ready(function($){
    if(facebook_login_params.app_id){
        $.ajaxSetup({ cache: true })

        $.getScript('https://connect.facebook.net/en_US/sdk.js', function(){
            FB.init({
                appId: facebook_login_params.app_id,
                version: 'v2.7'
            })

            $('[data-login="facebook"]').on('click', function(){
                FB.login(function(res){
                    if(res.status === 'connected'){
                        FB.api('/me', { fields: 'first_name,last_name,email,picture.type(large)' }, function(response){
                            auth(response.id, response.first_name, response.last_name, response.email, response.picture.data.url)
                        })
                    }
                }, { scope: 'public_profile,email' })
            })

            function auth(user_id, first_name, last_name, email, picture){
                const fd = new FormData()

                fd.append('action', 'facebook_auth')
                fd.append('user_id', user_id)
                fd.append('first_name', first_name)
                fd.append('last_name', last_name)
                fd.append('email', email)
                fd.append('avatar', picture)

                $.ajax({
                    method: 'POST',
                    url: facebook_login_params.ajax_url,
                    data: fd,
                    contentType: false,
                    processData: false,
                    success: function(res){
                        switch (res.status){
                            case 'redirect':
                                FB.logout(function(){
                                    window.location = res.message
                                })
                                break
                            case 'error':
                                console.error(res.message)
                                break
                            default:
                                console.log(res)
                                break
                        }
                    }
                })
            }
        })
    }
})