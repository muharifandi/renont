package com.nusatim.sapiriku.presentation.feature.common.selectregency

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.domain.model.Regencies
import com.nusatim.sapiriku.domain.usecase.GetRegenciesUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.Job
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class SelectRegencyViewModel @Inject constructor(
    private val getRegenciesUseCase: GetRegenciesUseCase
) : ViewModel() {

    private val _regencies = MutableStateFlow<UiState<List<Regencies>>>(UiState.Idle)
    val regencies = _regencies.asStateFlow()

    private var searchJob: Job? = null

    fun search(query: String) {
        searchJob?.cancel()
        searchJob = viewModelScope.launch {
            getRegenciesUseCase(query).collect { resource ->
                _regencies.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }
}
