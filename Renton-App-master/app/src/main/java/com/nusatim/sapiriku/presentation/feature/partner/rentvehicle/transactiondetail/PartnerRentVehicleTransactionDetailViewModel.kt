package com.nusatim.sapiriku.presentation.feature.partner.rentvehicle.transactiondetail
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.nusatim.sapiriku.domain.model.RentVehicleDetail
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.domain.usecase.CancelPartnerTransactionUseCase
import com.nusatim.sapiriku.domain.usecase.CompletePartnerTransactionUseCase
import com.nusatim.sapiriku.domain.usecase.GetPartnerTransactionDetailUseCase
import com.nusatim.sapiriku.domain.usecase.UpdatePartnerTransactionStatusUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

data class PartnerTransactionActionResult(val titleResId: Int? = null, val title: String? = null, val message: String?, val success: Boolean, val isComplete: Boolean = false)

@HiltViewModel
class PartnerRentVehicleTransactionDetailViewModel @Inject constructor(
    private val getPartnerTransactionDetailUseCase: GetPartnerTransactionDetailUseCase,
    private val cancelPartnerTransactionUseCase: CancelPartnerTransactionUseCase,
    private val updatePartnerTransactionStatusUseCase: UpdatePartnerTransactionStatusUseCase,
    private val completePartnerTransactionUseCase: CompletePartnerTransactionUseCase
) : ViewModel() {

    private val _detail = MutableStateFlow<UiState<RentVehicleDetail>>(UiState.Idle)
    val detail = _detail.asStateFlow()

    private val _actionResult = MutableStateFlow<PartnerTransactionActionResult?>(null)
    val actionResult = _actionResult.asStateFlow()

    fun loadDetail(id: Int) {
        viewModelScope.launch {
            getPartnerTransactionDetailUseCase(id).collect { resource ->
                _detail.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }

    fun cancelOrder(id: Int, titleResId: Int) {
        viewModelScope.launch {
            cancelPartnerTransactionUseCase(id).collect { resource ->
                if (resource is Resource.Success) {
                    _actionResult.value = PartnerTransactionActionResult(titleResId = titleResId, message = resource.data.message, success = resource.data.success)
                } else if (resource is Resource.Error) {
                    _actionResult.value = null
                }
            }
        }
    }

    fun updateStatus(id: Int, status: Int, title: String) {
        viewModelScope.launch {
            updatePartnerTransactionStatusUseCase(id, status).collect { resource ->
                if (resource is Resource.Success) {
                    _actionResult.value = PartnerTransactionActionResult(title = title, message = resource.data.message, success = resource.data.success)
                }
            }
        }
    }

    fun completeOrder(id: Int, titleResId: Int) {
        viewModelScope.launch {
            completePartnerTransactionUseCase(id).collect { resource ->
                if (resource is Resource.Success) {
                    _actionResult.value = PartnerTransactionActionResult(titleResId = titleResId, message = resource.data.message, success = resource.data.success, isComplete = true)
                }
            }
        }
    }

    fun consumeActionResult() {
        _actionResult.value = null
    }
}
