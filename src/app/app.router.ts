import { ModuleWithProviders } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { AppComponent } from './app.component';
import { WaccComponent } from './wacc/wacc.component';
import { WaccproComponent } from './waccpro/waccpro.component';

// export const router: Routes = [
const router: Routes = [
  { path: '', redirectTo: 'wacc', pathMatch: 'full'},
  { path: 'wacc', component: WaccComponent },
  { path: 'waccpro', component: WaccproComponent },
  // { path: '**', component: WaccComponent }
];

export const routes: ModuleWithProviders = RouterModule.forRoot(router);
