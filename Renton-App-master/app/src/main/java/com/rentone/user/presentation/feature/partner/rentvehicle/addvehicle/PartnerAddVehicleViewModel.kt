package com.rentone.user.presentation.feature.partner.rentvehicle.addvehicle
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.rentone.user.core.common.Resource
import com.rentone.user.core.common.UiState
import com.rentone.user.domain.model.*
import com.rentone.user.domain.model.command.UploadImageCommand
import com.rentone.user.domain.usecase.*
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class PartnerAddVehicleViewModel @Inject constructor(
    private val getPartnerVehicleInputConfigUseCase: GetPartnerVehicleInputConfigUseCase,
    private val getPartnerVehicleDetailUseCase: GetPartnerVehicleDetailUseCase,
    private val getPartnerVehicleModelsUseCase: GetPartnerVehicleModelsUseCase,
    private val postPartnerVehicleUseCase: PostPartnerVehicleUseCase,
    private val uploadPartnerVehicleImageUseCase: UploadPartnerVehicleImageUseCase,
    private val deletePartnerVehiclePhotoUseCase: DeletePartnerVehiclePhotoUseCase
) : ViewModel() {

    private val _config = MutableStateFlow<UiState<InputVehicleConfig>>(UiState.Idle)
    val config = _config.asStateFlow()

    private val _vehicleDetail = MutableStateFlow<UiState<Vehicle>>(UiState.Idle)
    val vehicleDetail = _vehicleDetail.asStateFlow()

    private val _models = MutableStateFlow<UiState<List<BasicData>>>(UiState.Idle)
    val models = _models.asStateFlow()

    private val _saveState = MutableStateFlow<UiState<OperationResult>>(UiState.Idle)
    val saveState = _saveState.asStateFlow()

    fun loadConfig(functionalType: Int) {
        viewModelScope.launch {
            getPartnerVehicleInputConfigUseCase(functionalType).collect { resource ->
                _config.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }

    fun loadVehicleDetail(id: Int) {
        viewModelScope.launch {
            getPartnerVehicleDetailUseCase(id).collect { resource ->
                _vehicleDetail.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }

    fun loadModels(brandId: Int) {
        viewModelScope.launch {
            getPartnerVehicleModelsUseCase(brandId).collect { resource ->
                _models.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }

    fun saveVehicle(form: Map<String, String>, files: List<String>) {
        viewModelScope.launch {
            postPartnerVehicleUseCase(form, files).collect { resource ->
                _saveState.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }

    suspend fun uploadPhoto(imagePath: String): Result<UploadImageResult> =
        uploadPartnerVehicleImageUseCase(UploadImageCommand(imagePath))

    suspend fun deletePhoto(id: Int): Result<Unit> = deletePartnerVehiclePhotoUseCase(id)
}
