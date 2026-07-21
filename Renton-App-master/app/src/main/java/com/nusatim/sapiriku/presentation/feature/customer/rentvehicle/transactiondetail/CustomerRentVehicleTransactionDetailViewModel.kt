package com.nusatim.sapiriku.presentation.feature.customer.rentvehicle.transactiondetail
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.nusatim.sapiriku.domain.model.RentVehicleDetail
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.domain.usecase.CancelCustomerTransactionUseCase
import com.nusatim.sapiriku.domain.usecase.GetCustomerTransactionDetailUseCase
import com.nusatim.sapiriku.domain.usecase.UpdateCustomerTransactionStatusUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

data class CustomerTransactionActionResult(val title: String, val message: String?, val success: Boolean, val isCancel: Boolean)

@HiltViewModel
class CustomerRentVehicleTransactionDetailViewModel @Inject constructor(
    private val getCustomerTransactionDetailUseCase: GetCustomerTransactionDetailUseCase,
    private val cancelCustomerTransactionUseCase: CancelCustomerTransactionUseCase,
    private val updateCustomerTransactionStatusUseCase: UpdateCustomerTransactionStatusUseCase
) : ViewModel() {

    private val _detail = MutableStateFlow<UiState<RentVehicleDetail>>(UiState.Idle)
    val detail = _detail.asStateFlow()

    private val _actionResult = MutableStateFlow<CustomerTransactionActionResult?>(null)
    val actionResult = _actionResult.asStateFlow()

    fun loadDetail(id: Int) {
        viewModelScope.launch {
            getCustomerTransactionDetailUseCase(id).collect { resource ->
                _detail.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }

    fun cancelOrder(id: Int, title: String) {
        viewModelScope.launch {
            cancelCustomerTransactionUseCase(id).collect { resource ->
                if (resource is Resource.Success) {
                    _actionResult.value = CustomerTransactionActionResult(title, resource.data.message, resource.data.success, isCancel = true)
                }
            }
        }
    }

    fun updateStatus(id: Int, status: Int, title: String) {
        viewModelScope.launch {
            updateCustomerTransactionStatusUseCase(id, status).collect { resource ->
                if (resource is Resource.Success) {
                    _actionResult.value = CustomerTransactionActionResult(title, resource.data.message, resource.data.success, isCancel = false)
                }
            }
        }
    }

    fun consumeActionResult() {
        _actionResult.value = null
    }
}
