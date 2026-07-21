package com.rentone.user.presentation.feature.register.partner.fragment
import android.net.Uri
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.activity.result.contract.ActivityResultContracts
import androidx.core.widget.doAfterTextChanged
import androidx.fragment.app.viewModels
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import coil.load
import com.rentone.user.R
import com.rentone.user.presentation.feature.common.locationpick.LocationPickActivity
import com.rentone.user.adapter.ArrayRegenciesAdapter
import com.rentone.user.databinding.FragmentRegisterPartnerStepOneBinding
import com.rentone.user.domain.model.Regencies
import com.rentone.user.presentation.feature.register.fragment.RegisterStepBaseFragment
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch

private const val OWNERSHIP_TYPE_COMPANY = 1

@AndroidEntryPoint
class RegisterPartnerStepOneFragment : RegisterStepBaseFragment() {

    private var _binding: FragmentRegisterPartnerStepOneBinding? = null
    private val binding get() = _binding!!

    private val viewModel: RegisterPartnerStepOneViewModel by viewModels()

    private var profilePhotoUri: Uri? = null
    private var selectedRegency: Regencies? = null
    private var latitude: Double? = null
    private var longitude: Double? = null

    private var companyNameValid = false
    private var addressValid = false
    private var regencyClickApplied = false

    private val pickProfilePhoto = registerForActivityResult(ActivityResultContracts.GetContent()) { uri: Uri? ->
        uri?.let {
            profilePhotoUri = it
            binding.imageUser.load(it) { error(R.drawable.user_image) }
        }
    }

    private val pickLocation = registerForActivityResult(ActivityResultContracts.StartActivityForResult()) { result ->
        if (result.resultCode == android.app.Activity.RESULT_OK) {
            latitude = result.data?.getDoubleExtra("latitude", 0.0)
            longitude = result.data?.getDoubleExtra("longitude", 0.0)
            binding.checkBussinessLocation.check()
        }
    }

    override fun onCreateView(inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?): View {
        _binding = FragmentRegisterPartnerStepOneBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        binding.imageUser.setOnClickListener { pickProfilePhoto.launch("image/*") }

        binding.inputCompanyName.doAfterTextChanged { validateCompanyName() }

        binding.inputRegency.doAfterTextChanged { text ->
            if (regencyClickApplied) {
                regencyClickApplied = false
                return@doAfterTextChanged
            }
            val value = text.toString()
            selectedRegency = null
            if (value.isEmpty()) {
                viewModel.resetRegency()
                binding.inputRegencyLayout.error = getString(R.string.regency_cannot_empty)
                binding.checkRegency.uncheck()
            } else {
                binding.inputRegencyLayout.error = getString(R.string.checking_regencies)
                binding.checkRegency.uncheck()
                viewModel.searchRegency(value)
            }
        }

        binding.inputAddress.doAfterTextChanged { validateAddress() }

        binding.btnPickLocation.setOnClickListener {
            pickLocation.launch(android.content.Intent(requireActivity(), LocationPickActivity::class.java))
        }

        observeState()
    }

    private fun observeState() {
        viewLifecycleOwner.lifecycleScope.launch {
            viewLifecycleOwner.repeatOnLifecycle(Lifecycle.State.STARTED) {
                viewModel.regencyState.collect { state ->
                    when (state) {
                        is RegencyQueryState.Found -> {
                            binding.inputRegencyLayout.error = getString(R.string.regency_must_select)
                            binding.checkRegency.uncheck()
                            val adapter = ArrayRegenciesAdapter(requireContext(), android.R.layout.simple_list_item_1, ArrayList(state.regencies))
                            binding.inputRegency.setAdapter(adapter)
                            binding.inputRegency.threshold = 1
                            binding.inputRegency.setOnItemClickListener { _, _, position, _ ->
                                val item = adapter.getItem(position)
                                selectedRegency = item
                                binding.inputRegencyLayout.error = null
                                binding.checkRegency.check()
                                regencyClickApplied = true
                                binding.inputRegency.setText(item.name)
                            }
                            if (state.regencies.size == 1 &&
                                state.regencies[0].name.equals(binding.inputRegency.text.toString(), ignoreCase = true)
                            ) {
                                val item = state.regencies[0]
                                selectedRegency = item
                                binding.inputRegencyLayout.error = null
                                binding.checkRegency.check()
                                regencyClickApplied = true
                                binding.inputRegency.setText(item.name)
                            }
                        }
                        is RegencyQueryState.NotFound -> {
                            selectedRegency = null
                            binding.inputRegencyLayout.error = getString(R.string.data_not_found)
                            binding.checkRegency.uncheck()
                        }
                        is RegencyQueryState.Error -> {
                            selectedRegency = null
                            binding.inputRegencyLayout.error = state.message ?: getString(R.string.failed_check_to_server)
                            binding.checkRegency.uncheck()
                        }
                        else -> Unit
                    }
                }
            }
        }
    }

    private fun validateCompanyName() {
        companyNameValid = binding.inputCompanyName.text.toString().isNotEmpty()
        binding.inputCompanyNameLayout.error = if (companyNameValid) null else getString(R.string.company_name_cannot_empty)
        if (companyNameValid) binding.checkCompanyName.check() else binding.checkCompanyName.uncheck()
    }

    private fun validateAddress() {
        addressValid = binding.inputAddress.text.toString().isNotEmpty()
        binding.inputAddressLayout.error = if (addressValid) null else getString(R.string.address_cannot_empty)
        if (addressValid) binding.checkAddress.check() else binding.checkAddress.uncheck()
    }

    override fun validateForm(): Boolean {
        validateCompanyName()
        if (binding.inputRegency.text.toString().isEmpty()) {
            binding.inputRegencyLayout.error = getString(R.string.regency_cannot_empty)
            binding.checkRegency.uncheck()
        }
        validateAddress()

        return companyNameValid && selectedRegency != null && addressValid && latitude != null && longitude != null
    }

    override fun getFormValue(): Map<String, String> = mapOf(
        "ownership_id" to OWNERSHIP_TYPE_COMPANY.toString(),
        "company_name" to binding.inputCompanyName.text.toString(),
        "description" to binding.inputDescription.text.toString(),
        "img_profile" to (profilePhotoUri?.toString() ?: ""),
        "regencies_id" to (selectedRegency?.id?.toString() ?: ""),
        "address" to binding.inputAddress.text.toString(),
        "latitude" to (latitude?.toString() ?: "0.0"),
        "longitude" to (longitude?.toString() ?: "0.0")
    )

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
