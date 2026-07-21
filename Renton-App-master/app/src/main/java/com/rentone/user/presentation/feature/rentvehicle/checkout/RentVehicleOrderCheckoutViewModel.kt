package com.rentone.user.presentation.feature.rentvehicle.checkout
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.rentone.user.core.common.Resource
import com.rentone.user.core.common.UiState
import com.rentone.user.domain.model.CheckoutDetail
import com.rentone.user.domain.model.OperationResult
import com.rentone.user.domain.model.Voucher
import com.rentone.user.domain.model.command.CheckoutCommand
import com.rentone.user.domain.usecase.CheckVoucherUseCase
import com.rentone.user.domain.usecase.GetCheckoutDetailUseCase
import com.rentone.user.domain.usecase.PostCheckoutUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.Job
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

sealed class VoucherState {
    data object Idle : VoucherState()
    data object Checking : VoucherState()
    data class Found(val voucher: Voucher) : VoucherState()
    data class NotFound(val message: String?) : VoucherState()
    data class Error(val message: String) : VoucherState()
}

@HiltViewModel
class RentVehicleOrderCheckoutViewModel @Inject constructor(
    private val getCheckoutDetailUseCase: GetCheckoutDetailUseCase,
    private val checkVoucherUseCase: CheckVoucherUseCase,
    private val postCheckoutUseCase: PostCheckoutUseCase
) : ViewModel() {

    private val _detail = MutableStateFlow<UiState<CheckoutDetail>>(UiState.Idle)
    val detail = _detail.asStateFlow()

    private val _voucherState = MutableStateFlow<VoucherState>(VoucherState.Idle)
    val voucherState = _voucherState.asStateFlow()

    private val _checkoutState = MutableStateFlow<UiState<OperationResult>>(UiState.Idle)
    val checkoutState = _checkoutState.asStateFlow()

    private var voucherJob: Job? = null

    fun loadDetail(vehicleId: Int, pricePackage: Int, startDate: String?, endDate: String?) {
        viewModelScope.launch {
            getCheckoutDetailUseCase(vehicleId, pricePackage, startDate, endDate).collect { resource ->
                _detail.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }

    fun checkVoucher(code: String, startDate: String?) {
        voucherJob?.cancel()
        if (code.isBlank()) {
            _voucherState.value = VoucherState.Idle
            return
        }
        voucherJob = viewModelScope.launch {
            checkVoucherUseCase(code, startDate).collect { resource ->
                _voucherState.value = when (resource) {
                    is Resource.Loading -> VoucherState.Checking
                    is Resource.Success -> {
                        val voucher = resource.data
                        if (voucher.status == 1) VoucherState.Found(voucher) else VoucherState.NotFound(null)
                    }
                    is Resource.Error -> VoucherState.Error(resource.message)
                    is Resource.Empty -> VoucherState.Idle
                }
            }
        }
    }

    fun postCheckout(command: CheckoutCommand) {
        viewModelScope.launch {
            postCheckoutUseCase(command).collect { resource ->
                _checkoutState.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }
}
