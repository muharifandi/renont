package com.nusatim.sapiriku.presentation.feature.chat.conversation
import android.media.MediaPlayer
import android.os.Bundle
import android.view.MenuItem
import android.widget.Toast
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.isVisible
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.nusatim.sapiriku.R
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.databinding.ActivityCustomerChatBinding
import com.nusatim.sapiriku.presentation.feature.chat.conversation.adapter.ListChatAdapter
import com.nusatim.sapiriku.core.util.ViewUtils
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import org.json.JSONObject
import com.nusatim.sapiriku.core.util.applyExitTransition

@AndroidEntryPoint
class ChatActivity : AppCompatActivity() {

    private lateinit var binding: ActivityCustomerChatBinding
    private val viewModel: ChatViewModel by viewModels()

    private var chatroomId: Int = 0
    private var partnerAccountId: Int = 0
    private var customerAccountId: Int = 0
    private var isPartner: Boolean = false
    
    private var sendSound: MediaPlayer? = null
    private var incomingSound: MediaPlayer? = null

    private lateinit var adapter: ListChatAdapter

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityCustomerChatBinding.inflate(layoutInflater)
        setContentView(binding.root)

        parseIntent()
        setupToolbar()
        setupRecyclerView()
        setupListeners()
        observeState()
        initSounds()

        viewModel.fetchChats(chatroomId, partnerAccountId, customerAccountId, isRefresh = true)
    }

    private fun parseIntent() {
        chatroomId = intent.getIntExtra("chatroom_id", 0)
        partnerAccountId = intent.getIntExtra("partner_account_id", 0)
        customerAccountId = intent.getIntExtra("customer_account_id", 0)
        isPartner = intent.getBooleanExtra("is_partner", false)
        
        binding.txtName.text = intent.getStringExtra("name")
    }

    private fun setupToolbar() {
        setSupportActionBar(binding.toolbar)
        supportActionBar?.setDisplayHomeAsUpEnabled(true)
        supportActionBar?.setDisplayShowHomeEnabled(true)
    }

    private fun setupRecyclerView() {
        adapter = ListChatAdapter(isPartner)
        val layoutManager = LinearLayoutManager(this).apply {
            reverseLayout = true
            stackFromEnd = true
        }
        binding.list.layoutManager = layoutManager
        binding.list.adapter = adapter
    }

    private fun setupListeners() {
        binding.btnSend.setOnClickListener {
            val message = binding.inputChat.text.toString()
            if (message.isNotBlank()) {
                val accountId = if (isPartner) partnerAccountId else customerAccountId
                val userType = if (isPartner) 4 else 5
                viewModel.sendMessage(chatroomId, accountId, userType, message, 0, null)
            }
        }
    }

    private fun observeState() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                viewModel.chats.collect { state ->
                    when (state) {
                        is UiState.Success -> {
                            adapter.submitList(state.data)
                            binding.list.scrollToPosition(0)
                        }
                        is UiState.Error -> {
                            Toast.makeText(this@ChatActivity, state.message, Toast.LENGTH_SHORT).show()
                        }
                        else -> Unit
                    }
                }
            }
        }
        
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                viewModel.sendMessageStatus.collect { state ->
                    if (state is UiState.Success) {
                        binding.inputChat.text?.clear()
                        sendSound?.start()
                        viewModel.fetchChats(chatroomId, partnerAccountId, customerAccountId, isRefresh = true)
                    }
                }
            }
        }
    }

    private fun initSounds() {
        sendSound = MediaPlayer.create(this, R.raw.send_chat)
        incomingSound = MediaPlayer.create(this, R.raw.incoming_chat)
    }

    override fun onOptionsItemSelected(item: MenuItem): Boolean {
        if (item.itemId == android.R.id.home) {
            onBackPressedDispatcher.onBackPressed()
            return true
        }
        return super.onOptionsItemSelected(item)
    }

    override fun onDestroy() {
        super.onDestroy()
        sendSound?.release()
        incomingSound?.release()
    }

    override fun finish() {
        super.finish()
        applyExitTransition(R.anim.slide_in_left, R.anim.slide_out_right)
    }
}
