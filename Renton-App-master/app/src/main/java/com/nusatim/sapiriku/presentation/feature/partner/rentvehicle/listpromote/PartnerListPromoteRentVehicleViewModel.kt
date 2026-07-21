package com.nusatim.sapiriku.presentation.feature.partner.rentvehicle.listpromote
import androidx.lifecycle.viewModelScope
import com.nusatim.sapiriku.domain.model.OperationResult
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.core.ui.PagedListViewModel
import com.nusatim.sapiriku.domain.model.FilterList
import com.nusatim.sapiriku.domain.model.PromoteVehicle
import com.nusatim.sapiriku.domain.usecase.CancelPartnerPromoteUseCase
import com.nusatim.sapiriku.domain.usecase.ListPartnerPromoteVehiclesUseCase
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
