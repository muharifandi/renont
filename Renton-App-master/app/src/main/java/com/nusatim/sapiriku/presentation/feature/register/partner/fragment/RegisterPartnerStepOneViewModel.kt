package com.nusatim.sapiriku.presentation.feature.register.partner.fragment
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.domain.model.Regencies
import com.nusatim.sapiriku.domain.usecase.GetRegenciesUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.Job
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

sealed class RegencyQueryState {
    data object Idle : RegencyQueryState()
    data object Checking : RegencyQueryState()
    data class Found(val regencies: List<Regencies>) : RegencyQueryState()
    data class NotFound(val message: String?) : RegencyQueryState()
    data class Error(val message: String?) : RegencyQueryState()
}

@HiltViewModel
class RegisterPartnerStepOneViewModel @Inject constructor(
    private val getRegenciesUseCase: GetRegenciesUseCase
) : ViewModel() {

    private val _regencyState = MutableStateFlow<RegencyQueryState>(RegencyQueryState.Idle)
    val regencyState = _regencyState.asStateFlow()

    private var regencyJob: Job? = null

    fun searchRegency(query: String) {
        regencyJob?.cancel()
        if (query.isEmpty()) {
            _regencyState.value = RegencyQueryState.Idle
            return
        }
        regencyJob = viewModelScope.launch {
            getRegenciesUseCase(query.uppercase()).collect { resource ->
                _regencyState.value = when (resource) {
                    is Resource.Loading -> RegencyQueryState.Checking
                    is Resource.Success -> if (resource.data.isNotEmpty()) {
                        RegencyQueryState.Found(resource.data)
                    } else {
                        RegencyQueryState.NotFound(null)
                    }
                    is Resource.Error -> RegencyQueryState.Error(resource.message)
                    is Resource.Empty -> RegencyQueryState.Idle
                }
            }
        }
    }

    fun resetRegency() {
        regencyJob?.cancel()
        _regencyState.value = RegencyQueryState.Idle
    }
}
