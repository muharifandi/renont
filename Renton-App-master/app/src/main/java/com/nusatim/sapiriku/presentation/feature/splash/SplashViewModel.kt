package com.nusatim.sapiriku.presentation.feature.splash

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.domain.model.ApplicationStatus
import com.nusatim.sapiriku.domain.usecase.CheckApplicationStatusUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.delay
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class SplashViewModel @Inject constructor(
    private val checkApplicationStatusUseCase: CheckApplicationStatusUseCase
) : ViewModel() {

    private val _appStatus = MutableStateFlow<UiState<ApplicationStatus>>(UiState.Idle)
    val appStatus = _appStatus.asStateFlow()

    fun checkApplicationStatus() {
        viewModelScope.launch {
            _appStatus.value = UiState.Loading
            delay(2000) // Minimum splash duration
            checkApplicationStatusUseCase().collect { resource ->
                _appStatus.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }
}
