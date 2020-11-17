// necesarios
import {ModuleWithProviders} from '@angular/core';
import {Routes, RouterModule} from '@angular/router';


// componentes
import {LoginComponent} from './components/login/login.component';
import {RegisterComponent} from './components/register/register.component';
import { HomeComponent } from './components/home/home.component';
import { ErrorComponent } from './components/error/error.component';

const appRoutes : Routes = [
    {path: '', component: LoginComponent},
    {path: 'inicio', component: LoginComponent},
    {path: 'login', component: LoginComponent},
    {path: 'registro', component: RegisterComponent},
    {path: 'home', component: HomeComponent},
    
    {path: '**', component: ErrorComponent},/*ULTIMA*/

];

export const appRoutingProviders: any[] = [];
export const routing: ModuleWithProviders = RouterModule.forRoot(appRoutes);

