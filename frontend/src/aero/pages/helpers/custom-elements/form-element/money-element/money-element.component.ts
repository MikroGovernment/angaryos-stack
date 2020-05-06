import { Component, EventEmitter, Input, Output } from '@angular/core';
import { AeroThemeHelper } from './../../../aero.theme';
import { BaseHelper } from './../../../base';

declare var $: any;

@Component(
{
    selector: 'money-element',
    styleUrls: ['./money-element.component.scss'],
    templateUrl: './money-element.component.html'
})
export class MoneyElementComponent
{
    @Input() default: string;
    @Input() value: string;
    @Input() name: string;
    @Input() class: string;
    @Input() placeholder: string;
    @Input() showFilterTypesSelect: boolean;
    @Input() filterType: string;
    @Input() type: string;

    val = "";
    unit = "";

    @Output() changed = new EventEmitter();

    constructor(private aeroThemeHelper: AeroThemeHelper) {}

    ngOnChanges()
    {
        this.val = this.value;
        this.unit = this.type.split(':')[1].toUpperCase();
    }
    
    ngAfterViewInit()
    {
        this.elementOperations();
    }

    elementOperations()
    {      
        $.getScript('assets/themes/aero/assets/plugins/jquery-inputmask/jquery.inputmask.bundle.js', 
            () => this.addInputMask());        
    }

    
    dataChanged(event)
    {
        var temp = $('#'+this.name+"-display").val();
        temp = temp.trim();
        temp = BaseHelper.replaceAll(temp, '.', '');
        temp = BaseHelper.replaceAll(temp, ',', '.');
        
        $('[name="'+this.name+'"]').val(temp);
        
        event.target = $('[name="'+this.name+'"]')[0];
        
        var params =
        {
            changed: this.changed,
            event: event
        };
        
        function func(params)
        {
            params.changed.emit(params.event);
        }

        return BaseHelper.doInterval(this.name+'MoneyFilterDataChanged', func, params, 1000);        
    }

    addInputMask()
    {
        $('[name="'+this.name+'-display"]').inputmask("currency", 
        {
            radixPoint: ",",
            rightAlign: false,
            //suffix: " €",
            prefix: "",
            oncomplete: (event) => this.dataChanged(event),
            onincomplete: (event) => this.dataChanged(event),
            oncleared: (event) => this.dataChanged(event)
        });
    }
}