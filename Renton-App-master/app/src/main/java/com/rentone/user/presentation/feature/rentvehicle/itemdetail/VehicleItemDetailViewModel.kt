package com.rentone.user.presentation.feature.rentvehicle.itemdetail
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.rentone.user.domain.model.VehicleDetail
import com.rentone.user.core.common.Resource
import com.rentone.user.core.common.UiState
import com.rentone.user.core.database.entity.UserEntity
import com.rentone.user.domain.repository.UserRepository
import com.rentone.user.domain.usecase.GetVehicleDetailUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.SharingStarted
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.stateIn
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class VehicleItemDetailViewModel @Inject constructor(
    private val getVehicleDetailUseCase: GetVehicleDetailUseCase,
    private val userRepository: UserRepository
) : ViewModel() {

    private val _detail = MutableStateFlow<UiState<VehicleDetail>>(UiState.Idle)
    val detail = _detail.asStateFlow()

    val currentUser: kotlinx.coroutines.flow.StateFlow<UserEntity?> = userRepository.getUser()
        .stateIn(viewModelScope, SharingStarted.WhileSubscribed(5000), null)

    fun loadDetail(id: Int) {
        viewModelScope.launch {
            getVehicleDetailUseCase(id).collect { resource ->
                _detail.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }
}
