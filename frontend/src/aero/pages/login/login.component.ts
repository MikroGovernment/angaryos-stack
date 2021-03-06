import { Component, TemplateRef } from '@angular/core';
import { Router } from '@angular/router';

import { BaseHelper } from './../helpers/base';
import { SessionHelper } from './../helpers/session';
import { MessageHelper } from './../helpers/message';
import { GeneralHelper } from './../helpers/general';
import { AeroThemeHelper } from './../helpers/aero.theme';
 
declare var $: any;

@Component(
{
    selector: 'aero-root',
    styleUrls: ['./login.component.scss'],
    templateUrl: './login.component.html',
})
export class LoginComponent 
{
    public loading = false;
    public baseUrl = "";

    public user = 
    {
        "email": "iletisim@omersavas.com",
        "password": "1234Aa."
    };

    constructor(
        private messageHelper: MessageHelper,
        private sessionHelper: SessionHelper,
        private generalHelper: GeneralHelper,
        private aeroThemeHelper: AeroThemeHelper,
        private router: Router
        )
    {
        if(BaseHelper.token.length > 0)
            window.location.href = BaseHelper.dashboardUrl;

        this.aeroThemeHelper.removeThemeClass();
        
        this.aeroThemeHelper.pageRutine();
        
        this.baseUrl = BaseHelper.backendBaseUrl + "#/";
        
        setTimeout(() => this.cookieControl(), 2000);
    }

    cookieControl()
    {
        var c = BaseHelper.readFromLocal('cookieControl');
        if(c == null)
            this.messageHelper.sweetAlert("Sitemizden en iyi şekilde faydalanabilmeniz için çerezler kullanılmaktadır. Bu siteye giriş yaparak çerez kullanımını kabul etmiş sayılıyorsunuz. Daha fazla bilgi için 'Güvenlik politikası' ve 'Aydınlatma Metni' sayfamızı ziyaret edebilirsiniz", "Çerez Kullanımı");
        
        BaseHelper.writeToLocal('cookieControl', true);
    }

    validate()
    {
        if(this.user.password.length < 4)
        {
            this.messageHelper.toastMessage("Şifre en az 4 karakter olmalı", "warning");
            return false;
        }
        else if(this.user.email.length < 4)
        {
            this.messageHelper.toastMessage("Mail en az 4 karakter olmalı", "warning");
            return false;
        }
        
        return true;
    }

    doLogin() 
    {
        if(!this.validate()) return;

        this.loading = true;

        this.sessionHelper.login(this.user.email, this.user.password)
        .then((data) => 
        {
            BaseHelper.setToken(data["token"]);
            this.sessionHelper.fillLoggedInUserInfo()
            .then((data) =>
            {
                this.loading = false;
                this.loadScript();
                window.location.href = BaseHelper.dashboardUrl;
            })
            .catch((e) =>
            {
                this.loading = false;
            });
        })
        .catch((errorMessage) =>  
        {
            this.loading = false;
            if(errorMessage == "***") return;
            this.messageHelper.toastMessage("Doğrulama Hatası: "+errorMessage);
        });
    }
    
    loadScript()
    {
        BaseHelper.writeToPipe('loadPageScriptsLoaded', false);

        setTimeout(() => 
        {
            BaseHelper.writeToPipe('loadPageScriptsLightLoaded', false);
            this.aeroThemeHelper.loadPageScriptsLight();
        }, 500);
    }
}
