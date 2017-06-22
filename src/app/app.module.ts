import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';

import { routes } from './app.router';

import { AppComponent } from './app.component';
import { WaccComponent } from './wacc/wacc.component';
import { WaccproComponent } from './waccpro/waccpro.component';

@NgModule({
  declarations: [
    AppComponent,
    WaccComponent,
    WaccproComponent
  ],
  imports: [
    BrowserModule,
    FormsModule,
    ReactiveFormsModule,
    routes
  ],
  providers: [],
  bootstrap: [AppComponent]
})
export class AppModule { }
