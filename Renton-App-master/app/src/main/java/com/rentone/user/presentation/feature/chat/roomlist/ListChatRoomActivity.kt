package com.rentone.user.presentation.feature.chat.roomlist
import android.app.Activity
import android.content.Intent
import android.os.Bundle
import android.view.MenuItem
import android.widget.Toast
import androidx.activity.result.contract.ActivityResultContracts
import androidx.activity.viewModels
import androidx.core.view.isVisible
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import androidx.recyclerview.widget.LinearLayoutManager
import com.rentone.user.R
import com.rentone.user.core.common.AppEvent
import com.rentone.user.core.common.AppEventBus
import com.rentone.user.core.common.Config
import com.rentone.user.custom.PaginationListener
import com.rentone.user.databinding.ActivityListChatroomBinding
import com.rentone.user.presentation.feature.chat.roomlist.adapter.ListChatroomAdapter
import com.rentone.user.presentation.feature.chat.conversation.ChatActivity
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import javax.inject.Inject
import com.rentone.user.core.util.applyExitTransition

@AndroidEntryPoint
class ListChatRoomActivity : AppCompatActivity() {

    @Inject lateinit var appEventBus: AppEventBus

    private lateinit var binding: ActivityListChatroomBinding
    private val viewModel: ListChatRoomViewModel by viewModels()
    private lateinit var adapter: ListChatroomAdapter

    private var isPartner = false

    private val chatActivityLauncher = registerForActivityResult(
        ActivityResultContracts.StartActivityForResult()
    ) { result ->
        if (result.resultCode == Activity.RESULT_OK) {
            viewModel.loadFirstPage()
        }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityListChatroomBinding.inflate(layoutInflater)
        setContentView(binding.root)

        supportActionBar?.apply {
            setDisplayHomeAsUpEnabled(true)
            elevation = 0f
        }

        isPartner = intent.getBooleanExtra("is_partner", false)
        viewModel.isPartner = isPartner

        adapter = ListChatroomAdapter(
            isPartner = isPartner,
            onItemClick = { chatroom, imagePath ->
                val intent = Intent(this, ChatActivity::class.java).apply {
                    putExtra("is_partner", isPartner)
                    putExtra("chatroom_id", chatroom.id)
                    putExtra("name", chatroom.name)
                    putExtra("image", imagePath)
                }
                chatActivityLauncher.launch(intent)
                applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
            }
        )

        val layoutManager = LinearLayoutManager(this)
        binding.list.layoutManager = layoutManager
        binding.list.adapter = adapter
        binding.list.addOnScrollListener(object : PaginationListener(layoutManager) {
            override fun loadMoreItems() = viewModel.loadMore()
            override fun isLastPage() = viewModel.isLastPage
            override fun isLoading() = viewModel.isLoadingMore
        })
        binding.srLayout.setOnRefreshListener { viewModel.loadFirstPage(isRefresh = true) }

        observeState()
        viewModel.loadFirstPage()
    }

    private fun observeState() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch {
                    viewModel.uiState.collect { state ->
                        binding.srLayout.isRefreshing = state.isRefreshing
                        adapter.setLoading(state.isLoadingMore)
                        adapter.submitList(state.items)

                        val isEmpty = state.items.isEmpty() && !state.isInitialLoading
                        binding.list.isVisible = !isEmpty
                        binding.txtListMessage.isVisible = isEmpty

                        if (state.error != null) {
                            Toast.makeText(this@ListChatRoomActivity, getString(R.string.failed_check_to_server), Toast.LENGTH_LONG).show()
                        }
                    }
                }
                launch {
                    appEventBus.events.collect { event ->
                        if (event is AppEvent.NewChatMessage) {
                            viewModel.loadFirstPage(isRefresh = true)
                        }
                    }
                }
            }
        }
    }

    override fun onOptionsItemSelected(item: MenuItem): Boolean {
        if (item.itemId == android.R.id.home) {
            onBackPressedDispatcher.onBackPressed()
        }
        return true
    }

    override fun finish() {
        setResult(Activity.RESULT_OK)
        super.finish()
        applyExitTransition(R.anim.slide_in_left, R.anim.slide_out_right)
    }
}
