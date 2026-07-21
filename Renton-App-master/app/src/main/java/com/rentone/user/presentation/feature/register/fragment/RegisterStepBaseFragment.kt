package com.rentone.user.presentation.feature.register.fragment

import androidx.fragment.app.Fragment

abstract class RegisterStepBaseFragment : Fragment() {

    open fun validateForm(): Boolean = true

    open fun getFormValue(): Map<String, String> = emptyMap()
}
