package com.rentone.user.presentation.feature.register.customer.fragment
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.rentone.user.core.common.Resource
import com.rentone.user.domain.usecase.CheckEmailUseCase
import com.rentone.user.domain.usecase.CheckPhoneUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.Job
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

sealed class FieldCheckState {
    data object Idle : FieldCheckState()
    data object Checking : FieldCheckState()
    data class Valid(val message: String? = null) : FieldCheckState()
    data class Invalid(val message: String?) : FieldCheckState()
}

@HiltViewModel
class RegisterCustomerStepOneViewModel @Inject constructor(
    private val checkEmailUseCase: CheckEmailUseCase,
    private val checkPhoneUseCase: CheckPhoneUseCase
) : ViewModel() {

    private val _emailState = MutableStateFlow<FieldCheckState>(FieldCheckState.Idle)
    val emailState = _emailState.asStateFlow()

    private val _phoneState = MutableStateFlow<FieldCheckState>(FieldCheckState.Idle)
    val phoneState = _phoneState.asStateFlow()

    private var emailJob: Job? = null
    private var phoneJob: Job? = null

    fun checkEmail(email: String) {
        emailJob?.cancel()
        emailJob = viewModelScope.launch {
            checkEmailUseCase(email).collect { resource ->
                _emailState.value = when (resource) {
                    is Resource.Loading -> FieldCheckState.Checking
                    is Resource.Success -> if (resource.data.useEmail) FieldCheckState.Valid() else FieldCheckState.Invalid(resource.data.message)
                    is Resource.Error -> FieldCheckState.Invalid(resource.message)
                    is Resource.Empty -> FieldCheckState.Idle
                }
            }
        }
    }

    fun checkPhone(phone: String) {
        phoneJob?.cancel()
        phoneJob = viewModelScope.launch {
            checkPhoneUseCase(phone).collect { resource ->
                _phoneState.value = when (resource) {
                    is Resource.Loading -> FieldCheckState.Checking
                    is Resource.Success -> if (resource.data.usePhone) FieldCheckState.Valid() else FieldCheckState.Invalid(resource.data.message)
                    is Resource.Error -> FieldCheckState.Invalid(resource.message)
                    is Resource.Empty -> FieldCheckState.Idle
                }
            }
        }
    }

    fun resetEmail() {
        emailJob?.cancel()
        _emailState.value = FieldCheckState.Idle
    }

    fun resetPhone() {
        phoneJob?.cancel()
        _phoneState.value = FieldCheckState.Idle
    }
}
