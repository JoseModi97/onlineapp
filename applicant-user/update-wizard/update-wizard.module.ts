import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule } from '@angular/forms';
import { UpdateWizardComponent } from './update-wizard.component';

@NgModule({
  declarations: [
    UpdateWizardComponent
  ],
  imports: [
    CommonModule,
    ReactiveFormsModule
  ],
  exports: [
    UpdateWizardComponent // Export if it needs to be used in other modules
  ]
})
export class UpdateWizardModule { }
