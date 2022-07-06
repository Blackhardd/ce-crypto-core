jQuery(document).ready(function($){
    if(true){
        $.ajaxSetup({ cache: true })

        $.getScript('https://accounts.google.com/gsi/client', function(){
            function handleCredentialResponse(response){
                console.log(response)
            }

            google.accounts.id.initialize({
                client_id: "8693697681-9hq4tvgqfhm8s59f0eru1pc2c9208cdr.apps.googleusercontent.com",
                callback: handleCredentialResponse
            })


            // google.accounts.id.renderButton(
            //     document.getElementById("buttonDiv"),
            //     { theme: "outline", size: "large" }
            // )
            
            google.accounts.id.prompt()
        })
    }
})