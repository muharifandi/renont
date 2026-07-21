package com.rentone.user.presentation.feature.partner.rentvehicle.listpromote
import androidx.lifecycle.viewModelScope
import com.rentone.user.domain.model.OperationResult
import com.rentone.user.core.common.Resource
import com.rentone.user.core.common.UiState
import com.rentone.user.core.ui.PagedListViewModel
import com.rentone.user.domain.model.FilterList
import com.rentone.user.domain.model.PromoteVehicle
import com.rentone.user.domain.usecase.CancelPartnerPromoteUseCase
import com.rentone.user.domain.usecase.ListPartnerPromoteVehiclesUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class PartnerListPromoteRentVehicleViewModel @Inject constructor(
    private val listPartnerPromoteVehiclesUseCase: ListPartnerPromoteVehiclesUseCase,
    private val cancelPartnerPromoteUseCase: CancelPartnerPromoteUseCase
) : PagedListViewModel<PromoteVehicle>() {

    var sortIndex: Int = 0
    var filterList: FilterList = FilterList()

    private val _cancelState = MutableStateFlow<UiState<OperationResult>>(UiState.Idle)
    val cancelState = _cancelState.asStateFlow()

    override suspend fun fetchPage(page: Int, pageSize: Int): Result<List<PromoteVehicle>> =
        listPartnerPromoteVehiclesUseCase(page, pageSize, sortIndex, filterList)

    fun cancelPromote(id: Int) {
        viewModelScope.launch {
            cancelPartnerPromoteUseCase(id).collect { resource ->
                _cancelState.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }
}
