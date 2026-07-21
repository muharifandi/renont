package com.nusatim.sapiriku.presentation.feature.account.exchangepoint
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.nusatim.sapiriku.domain.model.OperationResult
import com.nusatim.sapiriku.domain.model.ExchangePointConfig
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.domain.usecase.GetExchangePointConfigUseCase
import com.nusatim.sapiriku.domain.usecase.PostExchangePointUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class CustomerExcangePointViewModel @Inject constructor(
    private val getExchangePointConfigUseCase: GetExchangePointConfigUseCase,
    private val postExchangePointUseCase: PostExchangePointUseCase
) : ViewModel() {

    private val _config = MutableStateFlow<UiState<ExchangePointConfig>>(UiState.Idle)
    val config = _config.asStateFlow()

    private val _exchangeState = MutableStateFlow<UiState<OperationResult>>(UiState.Idle)
    val exchangeState = _exchangeState.asStateFlow()

    fun loadConfig() {
        viewModelScope.launch {
            getExchangePointConfigUseCase().collect { resource ->
                _config.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }

    fun exchangePoint(point: String) {
        viewModelScope.launch {
            postExchangePointUseCase(point).collect { resource ->
                _exchangeState.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }
}
