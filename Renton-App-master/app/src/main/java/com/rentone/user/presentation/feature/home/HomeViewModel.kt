package com.rentone.user.presentation.feature.home

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.rentone.user.core.common.Resource
import com.rentone.user.core.common.UiState
import com.rentone.user.domain.model.HomeData
import com.rentone.user.domain.repository.SessionRepository
import com.rentone.user.domain.usecase.GetHomeDataUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class HomeViewModel @Inject constructor(
    private val getHomeDataUseCase: GetHomeDataUseCase,
    private val sessionRepository: SessionRepository
) : ViewModel() {

    private val _homeState = MutableStateFlow<UiState<HomeData>>(UiState.Idle)
    val homeState = _homeState.asStateFlow()

    fun logout() {
        viewModelScope.launch { sessionRepository.clearSession() }
    }

    fun fetchHomeData() {
        viewModelScope.launch {
            getHomeDataUseCase().collect { resource ->
                _homeState.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }
}
