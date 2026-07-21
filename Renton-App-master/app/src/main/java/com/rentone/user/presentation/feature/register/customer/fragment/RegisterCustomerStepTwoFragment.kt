package com.rentone.user.presentation.feature.register.customer.fragment
import android.net.Uri
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.activity.result.contract.ActivityResultContracts
import androidx.core.widget.doAfterTextChanged
import coil.load
import com.rentone.user.R
import com.rentone.user.databinding.FragmentRegisterCustomerStepTwoBinding
import com.rentone.user.presentation.feature.register.fragment.RegisterStepBaseFragment
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class RegisterCustomerStepTwoFragment : RegisterStepBaseFragment() {

    private var _binding: FragmentRegisterCustomerStepTwoBinding? = null
    private val binding get() = _binding!!

    private var profilePhotoUri: Uri? = null
    private var identityPhotoUri: Uri? = null

    private var identityNumberValid = false

    private val pickProfilePhoto = registerForActivityResult(ActivityResultContracts.GetContent()) { uri: Uri? ->
        uri?.let {
            profilePhotoUri = it
            binding.imageUser.load(it) { error(R.drawable.user_image) }
        }
    }

    private val pickIdentityPhoto = registerForActivityResult(ActivityResultContracts.GetContent()) { uri: Uri? ->
        uri?.let {
            identityPhotoUri = it
            binding.imageIdentity.load(it) { error(R.drawable.ic_insert_drive_file_black_24dp) }
        }
    }

    override fun onCreateView(inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?): View {
        _binding = FragmentRegisterCustomerStepTwoBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        binding.inputIdentityNumber.doAfterTextChanged { validateIdentityNumber() }
        binding.imageUser.setOnClickListener { pickProfilePhoto.launch("image/*") }
        binding.imageIdentity.setOnClickListener { pickIdentityPhoto.launch("image/*") }
    }

    private fun validateIdentityNumber() {
        identityNumberValid = binding.inputIdentityNumber.text.toString().isNotEmpty()
        binding.inputIdentityNumberLayout.error = if (identityNumberValid) null else getString(R.string.identity_number_cannot_empty)
        if (identityNumberValid) binding.checkIdentityNumber.check() else binding.checkIdentityNumber.uncheck()
    }

    override fun validateForm(): Boolean {
        validateIdentityNumber()
        return identityNumberValid && identityPhotoUri != null
    }

    override fun getFormValue(): Map<String, String> = mapOf(
        "identity_number" to binding.inputIdentityNumber.text.toString(),
        "img_profile" to (profilePhotoUri?.toString() ?: ""),
        "img_identity" to (identityPhotoUri?.toString() ?: "")
    )

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
